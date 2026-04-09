<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Attribute;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use DualMedia\DtoRequestBundle\Provider\Interface\StandardObjectProviderInterface;

/**
 * Marker attribute, required with the {@link StandardObjectProviderInterface}.
 *
 * See the ProviderClosure type visible on {@link ProviderInterface} for the callable input.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsObjectProvider
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public string $class,
        public bool $default = false
    ) {
    }
}
