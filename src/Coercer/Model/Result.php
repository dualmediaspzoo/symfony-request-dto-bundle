<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Model;

use Symfony\Component\Validator\Constraint;

readonly class Result
{
    /**
     * @param \Closure(mixed): mixed $coerce transforms the value (handles collection wrapping internally)
     * @param list<Constraint> $constraints
     */
    public function __construct(
        public \Closure $coerce,
        public array $constraints = [],
        public self|null $inner = null
    ) {
    }
}
