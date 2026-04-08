<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    // coercion services
    $services->set(\DualMedia\DtoRequestBundle\Coercer\BooleanCoercer::class)
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\FloatCoercer::class)
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\IntegerCoercer::class)
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\StringCoercer::class)
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\DateTimeCoercer::class)
        ->arg('$stringCoercer', new Reference(\DualMedia\DtoRequestBundle\Coercer\StringCoercer::class))
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\Registry::class)
        ->arg('$locator', tagged_locator(DtoBundle::COERCER_TAG));

    $services->set(\DualMedia\DtoRequestBundle\Coercer\SupportValidator::class)
        ->arg('$registry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    // reflection services
    $services->set(\DualMedia\DtoRequestBundle\Reflection\MetaReflector::class);

    $services->set('dm.dto_bundle.type_resolver', \Symfony\Component\TypeInfo\TypeResolver\TypeResolver::class)
        ->factory([\Symfony\Component\TypeInfo\TypeResolver\TypeResolver::class, 'create']);

    $services->set(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class)
        ->arg('$validator', new Reference(\DualMedia\DtoRequestBundle\Coercer\SupportValidator::class));

    $services->set(\DualMedia\DtoRequestBundle\Reflection\VirtualReflector::class)
        ->arg('$propertyFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class));

    $services->set(\DualMedia\DtoRequestBundle\Reflection\Reflector::class)
        ->arg('$virtualReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\VirtualReflector::class))
        ->arg('$propertyFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class))
        ->arg('$metaReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\MetaReflector::class))
        ->arg('$typeResolver', new Reference('dm.dto_bundle.type_resolver'));

    // resolve services
    $services->set(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class)
        ->arg('$coercerRegistry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    // field handlers (priority determines evaluation order, highest first)
    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\CollectionDtoHandler::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Extractor::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 20]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\SingleDtoHandler::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Extractor::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 10]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\ScalarPropertyHandler::class)
        ->arg('$propertyResolver', new Reference(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 0]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Extractor::class)
        ->arg('$cacheReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class))
        ->arg('$handlers', tagged_iterator(DtoBundle::FIELD_HANDLER_TAG));

    $services->set(\DualMedia\DtoRequestBundle\Resolve\DtoResolver::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Extractor::class))
        ->arg('$validator', new Reference('validator'))
        ->public();

    // cache and warmers
    $services->set(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class)
        ->arg('$cache', new Reference('dm.dto_bundle.file_cache'))
        ->arg('$reflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\Reflector::class));

    $services->set(\DualMedia\DtoRequestBundle\Type\DtoCacheWarmer::class)
        ->tag('kernel.cache_warmer')
        ->arg('$dtoClassList', '%'.DtoBundle::DTO_LIST_PARAMETER.'%')
        ->arg('$cacheReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class));

    $services->set('dm.dto_bundle.file_cache', \Symfony\Component\Cache\Adapter\PhpFilesAdapter::class)
        ->arg('$namespace', 'dto_metadata')
        ->arg('$directory', '%kernel.cache_dir%/dm_dto_bundle');
};
