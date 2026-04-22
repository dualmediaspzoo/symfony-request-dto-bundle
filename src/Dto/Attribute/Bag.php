<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;

/**
 * Override which bag will be used for the property/dto.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
readonly class Bag
{
    public function __construct(
        public BagEnum $bag
    ) {
    }
}
