<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Model;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;

readonly class ResolvedValue
{
    public function __construct(
        public mixed $raw,
        public Result|null $coercion = null
    ) {
    }
}
