<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Entity;

class Product
{
    public function __construct(
        public readonly string $code,
        public readonly string $name
    ) {
    }
}
