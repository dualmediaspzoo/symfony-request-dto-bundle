<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults();

    $services->set(\DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto::class)
        ->tag(DtoBundle::DTO_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto::class)
        ->tag(DtoBundle::DTO_TAG);

    $services->set(\DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto::class)
        ->tag(DtoBundle::DTO_TAG);
};
