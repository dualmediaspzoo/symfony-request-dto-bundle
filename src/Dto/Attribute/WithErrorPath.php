<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Override the error path for entity property violations.
 *
 * Used alongside FindOneBy/FindBy to control which path appears
 * in validation errors when a property-level constraint fails.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class WithErrorPath
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        public string $path
    ) {
    }
}
