<?php

declare(strict_types=1);

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    $services->instanceof(AbstractDto::class)
        ->tag(DtoBundle::DTO_TAG);

    $services->load('DualMedia\\DtoRequestBundle\\Tests\\Fixture\\Dto\\', '../tests/Fixture/Dto/');
    $services->load('DualMedia\\DtoRequestBundle\\Tests\\Fixture\\Service\\', '../tests/Fixture/Service/');
};
