<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Symfony\Component\DependencyInjection\ServiceLocator;

class DynamicParameterRegistry
{
    /**
     * @param array<string, string> $map
     * @param ServiceLocator<callable(string): mixed> $locator
     */
    public function __construct(
        private readonly array $map,
        private readonly ServiceLocator $locator
    ) {
    }

    /**
     * @return callable(string): mixed
     *
     * @throws \RuntimeException
     */
    public function get(
        string $parameter
    ): callable {
        $index = $this->map[$parameter] ?? throw new \RuntimeException(sprintf('Unknown parameter %s fetch attempted', $parameter));

        return $this->locator->get($index);
    }
}
