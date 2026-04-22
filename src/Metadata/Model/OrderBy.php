<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class OrderBy
{
    public function __construct(
        public string $field,
        public string $order
    ) {
    }
}
