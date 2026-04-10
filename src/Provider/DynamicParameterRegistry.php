<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Symfony\Component\DependencyInjection\ServiceLocator;

class DynamicParameterRegistry
{
    /**
     * @param array<string, string> $map
     * @param array<string, string> $methods
     * @param ServiceLocator<object> $locator
     */
    public function __construct(
        private readonly array $map,
        private readonly array $methods,
        private readonly ServiceLocator $locator
    ) {
    }

    /**
     * @throws \RuntimeException
     */
    public function get(
        string $parameter
    ): mixed {
        $index = $this->map[$parameter] ?? throw new \RuntimeException(sprintf('Unknown parameter %s fetch attempted', $parameter));
        $method = $this->methods[$parameter];

        $service = $this->locator->get($index);

        return $service->$method($parameter);
    }
}
