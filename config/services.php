<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
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

    $services->set(\DualMedia\DtoRequestBundle\Coercer\Registry::class)
        ->arg('$locator', tagged_locator(DtoBundle::COERCER_TAG));

    $services->set(\DualMedia\DtoRequestBundle\Coercer\SupportValidator::class)
        ->arg('$registry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    // reflection services
    $services->set(\DualMedia\DtoRequestBundle\Reflection\TypeReflector::class);

    $services->set(\DualMedia\DtoRequestBundle\Reflection\Factory\TypeFactory::class);

    $services->set(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class)
        ->arg('$validator', new Reference(\DualMedia\DtoRequestBundle\Coercer\SupportValidator::class));

    $services->set(\DualMedia\DtoRequestBundle\Reflection\VirtualReflector::class)
        ->arg('$propertyFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class))
        ->arg('$typeFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\TypeFactory::class));

    $services->set(\DualMedia\DtoRequestBundle\Reflection\Reflector::class)
        ->arg('$propertyReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\TypeReflector::class))
        ->arg('$virtualReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\VirtualReflector::class))
        ->arg('$propertyFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory::class))
        ->arg('$typeFactory', new Reference(\DualMedia\DtoRequestBundle\Reflection\Factory\TypeFactory::class));

    // resolve services
    $services->set(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class)
        ->arg('$coercerRegistry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    $services->set(\DualMedia\DtoRequestBundle\Resolve\DtoResolver::class)
        ->arg('$propertyResolver', new Reference(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class))
        ->arg('$cacheReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class))
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
