<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

readonly class Path
{
    public function __construct(
        public string $path
    ) {
    }
}
