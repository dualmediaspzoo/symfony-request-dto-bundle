<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Entity/object lookup limiter.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Limit
{
    public function __construct(
        public int $count
    ) {
    }
}
