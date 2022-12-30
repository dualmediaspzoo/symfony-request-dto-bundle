<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AllowEnum implements DtoAttributeInterface
{
    /**
     * @param list<\BackedEnum> $allowed
     */
    public function __construct(
        public readonly array $allowed = []
    ) {
    }
}
