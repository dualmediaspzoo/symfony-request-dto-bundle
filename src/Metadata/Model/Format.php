<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Format
{
    public function __construct(
        public string $format
    ) {
    }
}
