<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class FindBy
{
    public function __construct(
        public bool $many = false
    ) {
    }
}
