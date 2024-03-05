<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Type;

use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;

/**
 * Transforms an unsure value into another type, useful for GET type-fixing and such.
 *
 * @template T
 */
interface CoercerInterface
{
    /**
     * Checks if coercion is possible for a type.
     */
    public function supports(
        Property $property
    ): bool;

    /**
     * Coerces a type into a different type.
     *
     * A result object will be returned with the constraint list if needed
     *
     * @return CoerceResult<T>
     */
    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value,
        bool $validatePropertyConstraints = false
    ): CoerceResult;
}
