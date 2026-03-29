<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Attribute;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;

/**
 * Marker attribute, required with the {@link ProviderInterface}.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class AsObjectProvider
{
    public function __construct(
        public bool $default = false
    ) {
    }
}
