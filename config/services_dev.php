<?php

use DualMedia\DtoRequestBundle\Interface\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Interface\Resolver\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Interface\Resolver\DtoTypeExtractorInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\TypeValidationInterface;
use DualMedia\DtoRequestBundle\Profiler\Service\Entity\ProfilingEntityProviderService;
use DualMedia\DtoRequestBundle\Profiler\Service\Resolver\ProfilingDtoResolverService;
use DualMedia\DtoRequestBundle\Profiler\Service\Resolver\ProfilingDtoTypeExtractorService;
use DualMedia\DtoRequestBundle\Profiler\Service\Validation\ProfilingTypeValidationService;
use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services()
        ->defaults()
        ->private();

    // resolver timer
    $services->set(ProfilingDtoResolverService::class)
        ->arg(0, new Reference(DtoResolverService::class))
        ->arg(1, new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    $services->alias(DtoResolverInterface::class, ProfilingDtoResolverService::class);

    // extractor timer
    $services->set(ProfilingDtoTypeExtractorService::class)
        ->arg(0, new Reference(DtoTypeExtractorHelper::class))
        ->arg(1, new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    $services->alias(DtoTypeExtractorInterface::class, ProfilingDtoTypeExtractorService::class);

    // entity provider
    $services->set(ProfilingEntityProviderService::class)
        ->arg(0, new Reference(EntityProviderService::class))
        ->arg(1, new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    $services->alias(ProviderServiceInterface::class, ProfilingEntityProviderService::class);

    // type validator
    $services->set(ProfilingTypeValidationService::class)
        ->arg(0, new Reference(TypeValidationHelper::class))
        ->arg(1, new Reference('debug.stopwatch', ContainerInterface::NULL_ON_INVALID_REFERENCE));
    $services->alias(TypeValidationInterface::class, ProfilingTypeValidationService::class);
};
