<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Metadata\BagEnum;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
readonly class Bag
{
    public function __construct(
        public BagEnum $bag
    ) {
    }
}
