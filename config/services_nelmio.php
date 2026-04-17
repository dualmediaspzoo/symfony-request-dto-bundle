<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\OpenApi\DtoRouteDescriber;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->set(FieldCollector::class)
        ->arg('$memoizer', new Reference(MainDtoMemoizerInterface::class))
        ->arg('$labelProcessors', tagged_locator(DtoBundle::LABEL_PROCESSOR_TAG));

    $services->set(SchemaBuilder::class);

    $services->set(DtoRouteDescriber::class)
        ->arg('$collector', new Reference(FieldCollector::class))
        ->arg('$builder', new Reference(SchemaBuilder::class))
        ->tag('nelmio_api_doc.route_describer')
        ->public();
};
