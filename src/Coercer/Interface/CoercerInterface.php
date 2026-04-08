<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Interface;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;

interface CoercerInterface
{
    /**
     * Builds a coercion pipeline for the given property.
     *
     * Returns a Result chain describing transformations
     * and constraints to apply during resolution.
     */
    public function coerce(
        Property $property
    ): Result;
}
