<?php

namespace DualMedia\DtoRequestBundle\Model\Type;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @template T
 */
class CoerceResult
{
    /**
     * @param T|array<int, T> $value
     * @param ConstraintViolationListInterface $violations
     */
    public function __construct(
        private readonly mixed $value,
        private readonly ConstraintViolationListInterface $violations = new ConstraintViolationList()
    ) {
    }

    /**
     * @return T|array<int, T>
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
