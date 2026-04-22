<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Model;

/**
 * Dynamic argument to be resolved by // todo.
 */
readonly class Dynamic
{
    public function __construct(
        public string $name
    ) {
    }
}
