<?php

namespace DM\DtoRequestBundle\Attributes\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;

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
