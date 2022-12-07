<?php

namespace DM\DtoRequestBundle\Model\Type;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @template T
 */
class CoerceResult
{
    /**
     * @var T|array<int, T>
     */
    private $value;

    /**
     * @phpstan-ignore-next-line
     */
    private ConstraintViolationListInterface $violations;

    /**
     * @param T|array<int, T> $value
     * @phpstan-ignore-next-line
     * @param ConstraintViolationListInterface|null $violations
     */
    public function __construct(
        $value,
        ?ConstraintViolationListInterface $violations = null
    ) {
        $this->value = $value;
        $this->violations = $violations ?? new ConstraintViolationList();
    }

    /**
     * @return T|array<int, T>
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @phpstan-ignore-next-line
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
