<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;

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
