<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;

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
