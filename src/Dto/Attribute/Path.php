<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Override expected path for property/dto.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Path
{
    public function __construct(
        public string $path
    ) {
    }
}
