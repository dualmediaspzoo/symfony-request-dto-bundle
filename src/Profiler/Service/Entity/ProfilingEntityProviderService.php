<?php

namespace DualMedia\DtoRequestBundle\Profiler\Service\Entity;

use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @extends AbstractWrapper<ProviderInterface>
 */
class ProfilingEntityProviderService extends AbstractWrapper implements ProviderServiceInterface
{
    public function __construct(
        private readonly ProviderServiceInterface $providerService,
        Stopwatch|null $stopwatch = null
    ) {
        parent::__construct($stopwatch);
    }

    #[\Override]
    public function getProvider(
        string $fqcn,
        string|null $providerId = null
    ): ProviderInterface {
        return $this->wrap(
            'provide:%d:'.$fqcn,
            fn () => $this->providerService->getProvider($fqcn, $providerId)
        );
    }
}
