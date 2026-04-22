<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

class Registry
{
    /**
     * @param ServiceLocator<CoercerInterface> $locator
     */
    public function __construct(
        private readonly ServiceLocator $locator
    ) {
    }

    public function get(
        string $coercer
    ): CoercerInterface {
        return $this->locator->get($coercer);
    }

    /**
     * @return ServiceLocator<CoercerInterface>
     */
    public function iterator(): ServiceLocator
    {
        return $this->locator;
    }
}
