<?php

use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DualMedia\DtoRequestBundle\DtoBundle as Bundle;
use DualMedia\DtoRequestBundle\EventSubscriber\HttpDtoActionSubscriber;
use DualMedia\DtoRequestBundle\Interfaces\Dynamic\ResolverServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ComplexLoaderServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\TargetProviderInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DualMedia\DtoRequestBundle\Interfaces\Type\CoercionServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\GroupServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DualMedia\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\DefaultProcessor;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\PascalCaseProcessor;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessorService;
use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use DualMedia\DtoRequestBundle\Service\Http\ActionValidatorService;
use DualMedia\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DualMedia\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Service\Resolver\DynamicResolverService;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\FloatCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DualMedia\DtoRequestBundle\Service\Type\CoercerService;
use DualMedia\DtoRequestBundle\Service\Validation\GroupProviderService;
use DualMedia\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use DualMedia\DtoRequestBundle\ValueResolver\DtoValueResolver;
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

    $services->alias(TargetProviderInterface::class, TargetProviderService::class);
    $services->set(TargetProviderService::class)
        ->arg(0, new Reference(ManagerRegistry::class));

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
        ->arg(1, new Reference(LabelProcessorServiceInterface::class))
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

    $services->alias(LabelProcessorServiceInterface::class, LabelProcessorService::class);
    $services->set(LabelProcessorService::class)
        ->arg(0, []);

    $services->set(DefaultProcessor::class)
        ->tag(Bundle::LABEL_PROCESSOR_TAB);

    $services->set(PascalCaseProcessor::class)
        ->tag(Bundle::LABEL_PROCESSOR_TAB);

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

    $services->set(interface_exists(\Symfony\Component\HttpKernel\Controller\ValueResolverInterface::class) ? DtoValueResolver::class : DtoArgumentResolver::class)
        ->arg(0, new Reference(DtoResolverInterface::class))
        ->arg(1, new Reference('event_dispatcher'))
        ->tag('controller.argument_value_resolver');

    $services->set(DtoOADescriber::class)
        ->arg(0, new Reference(DtoTypeExtractorInterface::class))
        ->arg(1, new Reference(LabelProcessorServiceInterface::class))
        ->tag('nelmio_api_doc.route_describer');

    // Subscribers
    $services->set(HttpDtoActionSubscriber::class)
        ->tag('kernel.event_subscriber');
};
