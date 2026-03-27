<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Type
{
    /**
     * @param class-string|null $fqcn
     */
    public function __construct(
        public string $type,
        public string|null $fqcn = null
    ) {
    }
}
