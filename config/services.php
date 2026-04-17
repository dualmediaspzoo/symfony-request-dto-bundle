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

    $services->set(\DualMedia\DtoRequestBundle\Coercer\UploadedFileCoercer::class)
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\DateTimeCoercer::class)
        ->arg('$stringCoercer', new Reference(\DualMedia\DtoRequestBundle\Coercer\StringCoercer::class))
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\EnumCoercer::class)
        ->arg('$stringCoercer', new Reference(\DualMedia\DtoRequestBundle\Coercer\StringCoercer::class))
        ->arg('$integerCoercer', new Reference(\DualMedia\DtoRequestBundle\Coercer\IntegerCoercer::class))
        ->arg('$labelProcessorLocator', tagged_locator(DtoBundle::LABEL_PROCESSOR_TAG))
        ->tag(DtoBundle::COERCER_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Coercer\Registry::class)
        ->arg('$locator', tagged_locator(DtoBundle::COERCER_TAG));

    $services->set(\DualMedia\DtoRequestBundle\Coercer\SupportValidator::class)
        ->arg('$registry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    $services->set(\DualMedia\DtoRequestBundle\Provider\EntityProviderRegistry::class)
        ->arg('$registry', new Reference('doctrine'))
        ->arg('$queryCreator', new Reference('dm.dto_bundle.query_creator'))
        ->arg('$referenceHelper', new Reference('dm.dto_bundle.reference_helper'))
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set('dm.dto_bundle.query_creator', \DualMedia\DoctrineQueryCreator\QueryCreator::class);
    $services->set('dm.dto_bundle.reference_helper', \DualMedia\DoctrineQueryCreator\ReferenceHelper::class);

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
        ->arg('$typeResolver', new Reference('dm.dto_bundle.type_resolver'))
        ->arg('$groupProviderLocator', tagged_locator(DtoBundle::GROUP_PROVIDER_TAG))
        ->arg('$objectProviderLocator', tagged_locator(DtoBundle::OBJECT_PROVIDER_TAG));

    $services->set(\DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry::class)
        ->public();

    // resolve services
    $services->set(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class)
        ->arg('$coercerRegistry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class));

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Label\PascalCaseProcessor::class)
        ->tag(DtoBundle::LABEL_PROCESSOR_TAG);

    // field handlers (priority determines evaluation order, highest first)
    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\CollectionDtoHandler::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface::class))
        ->arg('$memoizer', new Reference(\DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 20]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\SingleDtoHandler::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface::class))
        ->arg('$memoizer', new Reference(\DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 10]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\EntityPropertyHandler::class)
        ->arg('$propertyResolver', new Reference(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class))
        ->arg('$entityProviderRegistry', new Reference(\DualMedia\DtoRequestBundle\Provider\EntityProviderRegistry::class))
        ->arg('$coercerRegistry', new Reference(\DualMedia\DtoRequestBundle\Coercer\Registry::class))
        ->arg('$dynamicParameterRegistry', new Reference(\DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry::class))
        ->arg('$objectProviderLocator', tagged_locator(DtoBundle::OBJECT_PROVIDER_TAG))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 5]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Handler\ScalarPropertyHandler::class)
        ->arg('$propertyResolver', new Reference(\DualMedia\DtoRequestBundle\Resolve\PropertyResolver::class))
        ->tag(DtoBundle::FIELD_HANDLER_TAG, ['priority' => 0]);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\Extractor::class)
        ->arg('$handlers', tagged_iterator(DtoBundle::FIELD_HANDLER_TAG))
        ->arg('$dispatcher', new Reference('event_dispatcher'));
    $services->alias(\DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface::class, \DualMedia\DtoRequestBundle\Resolve\Extractor::class);

    $services->set(\DualMedia\DtoRequestBundle\Resolve\ViolationMapper::class)
        ->arg('$memoizer', new Reference(\DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface::class));

    $services->set(\DualMedia\DtoRequestBundle\Resolve\DtoResolver::class)
        ->arg('$extractor', new Reference(\DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface::class))
        ->arg('$memoizer', new Reference(\DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface::class))
        ->arg('$validator', new Reference('validator'))
        ->arg('$groupProviderLocator', tagged_locator(DtoBundle::GROUP_PROVIDER_TAG))
        ->arg('$violationMapper', new Reference(\DualMedia\DtoRequestBundle\Resolve\ViolationMapper::class))
        ->public();
    $services->alias(\DualMedia\DtoRequestBundle\Resolve\Interface\DtoResolverInterface::class, \DualMedia\DtoRequestBundle\Resolve\DtoResolver::class)
        ->public();

    $services->set(\DualMedia\DtoRequestBundle\ValueResolver\DtoValueResolver::class)
        ->arg('$dtoResolver', new Reference(\DualMedia\DtoRequestBundle\Resolve\Interface\DtoResolverInterface::class))
        ->arg('$eventDispatcher', new Reference('event_dispatcher'))
        ->tag('controller.argument_value_resolver', ['priority' => 50]);

    // cache and warmers
    $services->set(\DualMedia\DtoRequestBundle\Reflection\RuntimeResolve::class)
        ->arg('$reflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\Reflector::class));
    $services->alias(\DualMedia\DtoRequestBundle\Reflection\Interface\RuntimeResolveInterface::class, \DualMedia\DtoRequestBundle\Reflection\RuntimeResolve::class);

    $services->set(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class)
        ->arg('$cache', new Reference('dm.dto_bundle.file_cache'))
        ->arg('$reflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\Reflector::class))
        ->arg('$runtimeHelper', new Reference(\DualMedia\DtoRequestBundle\Reflection\Interface\RuntimeResolveInterface::class));

    $services->set(\DualMedia\DtoRequestBundle\Reflection\MainDtoMemoizer::class)
        ->arg('$cacheReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class))
        ->tag('kernel.reset', ['method' => 'reset']);
    $services->alias(\DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface::class, \DualMedia\DtoRequestBundle\Reflection\MainDtoMemoizer::class);

    $services->set(\DualMedia\DtoRequestBundle\Type\DtoCacheWarmer::class)
        ->tag('kernel.cache_warmer')
        ->arg('$dtoClassList', '%'.DtoBundle::DTO_LIST_PARAMETER.'%')
        ->arg('$cacheReflector', new Reference(\DualMedia\DtoRequestBundle\Reflection\CacheReflector::class));

    $services->set('dm.dto_bundle.file_cache', \Symfony\Component\Cache\Adapter\PhpFilesAdapter::class)
        ->arg('$namespace', 'dto_metadata')
        ->arg('$directory', '%kernel.cache_dir%/dm_dto_bundle');

    // event subscribers
    $services->set(\DualMedia\DtoRequestBundle\Dto\EventSubscriber\ControllerSubscriber::class)
        ->arg('$dispatcher', new Reference('event_dispatcher'))
        ->tag('kernel.event_subscriber');

    $services->set(\DualMedia\DtoRequestBundle\Dto\EventSubscriber\ActionSubscriber::class)
        ->arg('$dispatcher', new Reference('event_dispatcher'))
        ->tag('kernel.event_subscriber');
};
