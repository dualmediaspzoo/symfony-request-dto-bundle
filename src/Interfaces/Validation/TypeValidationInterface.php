<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Validation;

use DualMedia\DtoRequestBundle\Model\Type\Property as PropertyTypeModel;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface TypeValidationInterface
{
    /**
     * @param array<string, mixed> $values
     * @param array<string, PropertyTypeModel> $properties
     *
     * @phpstan-ignore-next-line
     * @return ConstraintViolationListInterface
     */
    public function validateType(
        array &$values,
        array $properties
    ): ConstraintViolationListInterface;
}
