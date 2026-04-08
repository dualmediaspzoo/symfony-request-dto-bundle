<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Model;

use Symfony\Component\Validator\Constraint;

readonly class Result
{
    /**
     * @param list<Constraint> $constraints
     */
    public function __construct(
        public mixed $value,
        public array $constraints = [],
        public self|null $inner = null
    ) {
    }
}
