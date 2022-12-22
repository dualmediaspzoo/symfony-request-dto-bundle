<?php

namespace DM\DtoRequestBundle\Profiler\Service\Entity;

use DM\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DM\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @extends AbstractWrapper<ProviderInterface>
 */
class ProfilingEntityProviderService extends AbstractWrapper implements ProviderServiceInterface
{
    private ProviderServiceInterface $providerService;

    public function __construct(
        ProviderServiceInterface $providerService,
        ?Stopwatch $stopwatch = null
    ) {
        $this->providerService = $providerService;
        parent::__construct($stopwatch);
    }

    public function getProvider(
        string $fqcn,
        ?string $providerId = null
    ): ProviderInterface {
        return $this->wrap(
            'provide:%d:'.$fqcn,
            fn () => $this->providerService->getProvider($fqcn, $providerId)
        );
    }
}
