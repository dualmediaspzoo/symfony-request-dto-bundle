<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->set(\DualMedia\DtoRequestBundle\Type\DtoCacheWarmer::class)
        ->tag('kernel.cache_warmer');
};
