<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Allow only expected enum values.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class AllowEnum
{
    /**
     * @param non-empty-list<\BackedEnum> $allowed
     */
    public function __construct(
        public array $allowed
    ) {
    }
}
