<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Allow only expected enum values.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class WithAllowedEnum
{
    /**
     * @param \UnitEnum|non-empty-list<\UnitEnum> $allowed
     */
    public function __construct(
        public \UnitEnum|array $allowed
    ) {
    }
}
