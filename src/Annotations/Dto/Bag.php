<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Enum\BagEnum;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
class Bag implements DtoAnnotationInterface
{
    /**
     * @param BagEnum $bag Specifies in which part of the request to expect data for this dto
     */
    public function __construct(
        public readonly BagEnum $bag = BagEnum::Request
    ) {
    }
}
