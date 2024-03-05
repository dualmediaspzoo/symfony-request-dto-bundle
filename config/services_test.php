<?php

use DualMedia\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\EventSubscriber\HttpDtoActionSubscriber;
use DualMedia\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DualMedia\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\FloatCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DualMedia\DtoRequestBundle\Service\Type\CoercerService;
use DualMedia\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use DualMedia\DtoRequestBundle\Tests\Service\Entity\DummyModelProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

return static function (ContainerConfigurator $configurator) {
    $fn = include __DIR__.'/services_dev.php';

    $fn($configurator);

    $services = $configurator->services()
        ->defaults()
        ->private();

    // I am unsure why but the phpdoc reflector is missing in tests
    $services->set('property_info.phpdoc_extractor', PhpDocExtractor::class)
        ->tag('property_info.type_extractor');

    $makePublic = [
        DtoResolverService::class,
        TypeValidationHelper::class,
        DtoTypeExtractorHelper::class,

        BoolCoercer::class,
        FloatCoercer::class,
        IntCoercer::class,
        StringCoercer::class,
        DateTimeImmutableCoercer::class,
        EnumCoercer::class,
        CoercerService::class,
        UploadedFileCoercer::class,

        DtoOADescriber::class,

        DtoArgumentResolver::class,

        OnNullActionValidator::class,
        HttpDtoActionSubscriber::class,
    ];

    foreach ($makePublic as $id) {
        $services->get($id)
            ->public();
    }

    $services->set(DummyModelProvider::class)
        ->public()
        ->tag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG);
};
