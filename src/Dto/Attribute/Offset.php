<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Entity/object offset count.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Offset
{
    public function __construct(
        public int $count
    ) {
    }
}
