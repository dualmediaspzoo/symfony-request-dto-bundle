<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * DateTime format specifier for date parsing.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Format
{
    public function __construct(
        public string $format
    ) {
    }
}
