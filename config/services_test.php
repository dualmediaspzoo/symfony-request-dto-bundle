<?php

use DM\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DM\DtoRequestBundle\DtoBundle;
use DM\DtoRequestBundle\EventSubscriber\HttpDtoActionSubscriber;
use DM\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DM\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use DM\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DM\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DM\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\FloatCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DM\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DM\DtoRequestBundle\Service\Type\CoercerService;
use DM\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use DM\DtoRequestBundle\Tests\Service\Entity\DummyModelProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;

return static function (ContainerConfigurator $configurator) {
    $fn = include __DIR__ . '/services_dev.php';

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
