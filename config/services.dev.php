<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Profiler\ProfilingDtoResolver;
use DualMedia\DtoRequestBundle\Profiler\ProfilingExtractor;
use DualMedia\DtoRequestBundle\Profiler\ProfilingMainDtoMemoizer;
use DualMedia\DtoRequestBundle\Profiler\ProfilingRuntimeResolve;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use DualMedia\DtoRequestBundle\Reflection\MainDtoMemoizer;
use DualMedia\DtoRequestBundle\Reflection\RuntimeResolve;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Resolve\Extractor;
use DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface;
use DualMedia\DtoRequestBundle\Resolve\ViolationMapper;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->set(DtoDataCollector::class)
        ->tag('data_collector', [
            'template' => '@Dto/profiler/dto.html.twig',
            'id' => 'dm_dto',
            'priority' => 255,
        ]);

    $services->set(ProfilingExtractor::class)
        ->decorate(Extractor::class)
        ->arg('$inner', new Reference('.inner'))
        ->arg('$stopwatch', new Reference('debug.stopwatch'))
        ->arg('$collector', new Reference(DtoDataCollector::class));

    $services->set(ProfilingMainDtoMemoizer::class)
        ->decorate(MainDtoMemoizer::class)
        ->arg('$inner', new Reference('.inner'))
        ->arg('$stopwatch', new Reference('debug.stopwatch'))
        ->arg('$collector', new Reference(DtoDataCollector::class))
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->set(ProfilingRuntimeResolve::class)
        ->decorate(RuntimeResolve::class)
        ->arg('$inner', new Reference('.inner'))
        ->arg('$stopwatch', new Reference('debug.stopwatch'))
        ->arg('$collector', new Reference(DtoDataCollector::class));

    // ProfilingDtoResolver extends DtoResolver; we re-declare all parent args plus the profiling ones
    $services->set(ProfilingDtoResolver::class)
        ->decorate(DtoResolver::class)
        ->arg('$extractor', new Reference(ExtractorInterface::class))
        ->arg('$memoizer', new Reference(MainDtoMemoizerInterface::class))
        ->arg('$validator', new Reference('validator'))
        ->arg('$groupProviderLocator', tagged_locator(DtoBundle::GROUP_PROVIDER_TAG))
        ->arg('$violationMapper', new Reference(ViolationMapper::class))
        ->arg('$stopwatch', new Reference('debug.stopwatch'))
        ->arg('$collector', new Reference(DtoDataCollector::class))
        ->public();
};
