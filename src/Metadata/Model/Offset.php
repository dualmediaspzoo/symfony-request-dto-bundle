<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Offset
{
    public function __construct(
        public int $count
    ) {
    }
}
