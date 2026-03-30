<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Model;

/**
 * Literal value that will be passed as-is to the field.
 */
readonly class Literal
{
    public function __construct(
        public mixed $value
    ) {
    }
}
