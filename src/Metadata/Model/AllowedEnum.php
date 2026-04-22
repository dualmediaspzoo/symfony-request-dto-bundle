<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class AllowedEnum
{
    /**
     * @param non-empty-list<\UnitEnum> $allowed
     */
    public function __construct(
        public array $allowed
    ) {
    }
}
