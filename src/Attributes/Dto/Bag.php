<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
class Bag implements DtoAttributeInterface
{
    /**
     * @param BagEnum $bag Specifies in which part of the request to expect data for this dto
     */
    public function __construct(
        public readonly BagEnum $bag = BagEnum::Request
    ) {
    }
}
