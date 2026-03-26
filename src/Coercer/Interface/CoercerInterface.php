<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Interface;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Property;

interface CoercerInterface
{
    /**
     * Transforms a raw value into the target type.
     *
     * Returns the coerced value and any constraints
     * to be validated in the batch validation pass.
     */
    public function coerce(
        Property $property,
        mixed $value
    ): Result;
}
