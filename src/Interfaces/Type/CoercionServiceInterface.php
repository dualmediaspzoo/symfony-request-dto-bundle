<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Type;

use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;

/**
 * Combined service interface for coercers.
 *
 * @template T
 */
interface CoercionServiceInterface
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
     * @return CoerceResult<T>|null
     */
    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value
    ): CoerceResult|null;
}
