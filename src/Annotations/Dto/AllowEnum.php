<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AllowEnum implements DtoAnnotationInterface
{
    /**
     * @param list<\BackedEnum> $allowed
     */
    public function __construct(
        public readonly array $allowed = []
    ) {
    }
}
