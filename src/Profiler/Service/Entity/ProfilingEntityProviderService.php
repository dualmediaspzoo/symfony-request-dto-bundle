<?php

namespace DualMedia\DtoRequestBundle\Profiler\Service\Entity;

use DualMedia\DtoRequestBundle\Interface\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @extends AbstractWrapper<ProviderInterface<object>>
 *
 * @implements ProviderServiceInterface<object>
 */
class ProfilingEntityProviderService extends AbstractWrapper implements ProviderServiceInterface
{
    /**
     * @param ProviderServiceInterface<object> $providerService
     */
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
