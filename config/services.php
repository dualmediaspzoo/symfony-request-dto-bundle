<?php

use DM\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DM\DtoRequestBundle\DtoBundle as Bundle;
use DM\DtoRequestBundle\EventSubscriber\HttpDtoActionSubscriber;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverServiceInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ComplexLoaderServiceInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DM\DtoRequestBundle\Interfaces\Type\CoercionServiceInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupServiceInterface;
use DM\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DM\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use DM\DtoRequestBundle\Service\Entity\EntityProviderService;
use DM\DtoRequestBundle\Service\Http\ActionValidatorService;
use DM\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DM\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use DM\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DM\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DM\DtoRequestBundle\Service\Resolver\DynamicResolverService;
use DM\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\FloatCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DM\DtoRequestBundle\Service\Type\CoercerService;
use DM\DtoRequestBundle\Service\Validation\GroupProviderService;
use DM\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->alias(TypeValidationInterface::class, TypeValidationHelper::class);
    $services->set(TypeValidationHelper::class)
        ->arg(0, new Reference(CoercionServiceInterface::class));

    $services->alias(ProviderServiceInterface::class, EntityProviderService::class);
    $services->set(EntityProviderService::class)
        ->arg(0, []);

    $services->alias(GroupServiceInterface::class, GroupProviderService::class);
    $services->set(GroupProviderService::class)
        ->arg(0, new TaggedIteratorArgument(Bundle::GROUP_PROVIDER_TAG));

    // coercion services
    $services->alias(CoercionServiceInterface::class, CoercerService::class);
    $services->set(BoolCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(FloatCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(IntCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(StringCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(EnumCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(DateTimeImmutableCoercer::class)
        ->arg(0, \DateTimeInterface::ATOM) // todo: allow the bundle to configure a default date format
        ->arg(1, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(UploadedFileCoercer::class)
        ->arg(0, new Reference('validator'))
        ->tag(Bundle::COERCER_TAG);

    $services->set(CoercerService::class)
        ->arg(0, new TaggedIteratorArgument(Bundle::COERCER_TAG))
        ->arg(1, new Reference('validator'));

    $services->alias(ResolverServiceInterface::class, DynamicResolverService::class);
    $services->set(DynamicResolverService::class)
        ->arg(0, new TaggedIteratorArgument(Bundle::DYNAMIC_RESOLVER_TAG));

    $services->alias(ComplexLoaderServiceInterface::class, ComplexLoaderService::class);
    $services->set(ComplexLoaderService::class)
        ->arg(0, [])
        ->arg(1, new Reference(ProviderServiceInterface::class));

    // HTTP Action validators
    $services->set(OnNullActionValidator::class)
        ->tag(Bundle::HTTP_ACTION_VALIDATOR_TAG);
    $services->set(ActionValidatorService::class)
        ->arg(0, new TaggedIteratorArgument(Bundle::HTTP_ACTION_VALIDATOR_TAG));

    $services->alias(DtoTypeExtractorInterface::class, DtoTypeExtractorHelper::class);
    $services->set(DtoTypeExtractorHelper::class)
        ->arg(0, new Reference('property_info'));

    $services->alias(DtoResolverInterface::class, DtoResolverService::class);
    $services->set(DtoResolverService::class)
        ->arg(0, new Reference(TypeValidationInterface::class))
        ->arg(1, new Reference(DtoTypeExtractorInterface::class))
        ->arg(2, new Reference(ProviderServiceInterface::class))
        ->arg(3, new Reference(GroupServiceInterface::class))
        ->arg(4, new Reference(ComplexLoaderServiceInterface::class))
        ->arg(5, new Reference(ResolverServiceInterface::class))
        ->arg(6, new Reference(ActionValidatorService::class))
        ->arg(7, new Reference('validator'));

    $services->set(DtoArgumentResolver::class)
        ->arg(0, new Reference(DtoResolverInterface::class))
        ->arg(1, new Reference('event_dispatcher'))
        ->tag('controller.argument_value_resolver');

    $services->set(DtoOADescriber::class)
        ->arg(0, new Reference(DtoTypeExtractorInterface::class))
        ->tag('nelmio_api_doc.route_describer');

    // Subscribers
    $services->set(HttpDtoActionSubscriber::class)
        ->tag('kernel.event_subscriber');
};
