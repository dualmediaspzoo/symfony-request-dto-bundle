<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Type
{
    public function __construct(
        public string $type,
        public bool $collection,
        public string|null $fqcn = null,
        public string|null $subType = null
    ) {
    }
}
