<?php

namespace DM\DtoRequestBundle\Interfaces\Type;

use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;

/**
 * Combined service interface for coercers
 *
 * @template T
 */
interface CoercionServiceInterface
{
    /**
     * Checks if coercion is possible for a type
     *
     * @param Property $property
     *
     * @return bool
     */
    public function supports(
        Property $property
    ): bool;

    /**
     * Coerces a type into a different type
     *
     * A result object will be returned with the constraint list if needed
     *
     * @param string $propertyPath
     * @param Property $property
     * @param mixed $value
     *
     * @return CoerceResult<T>|null
     */
    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): ?CoerceResult;
}
