<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Interface;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\Validator\Constraint;

interface CoercerInterface
{
    /**
     * Builds a coercion pipeline for the given property.
     *
     * Returns a Result chain describing transformations
     * and constraints to apply during resolution.
     *
     * @param Constraint|list<Constraint> $constraints additional constraints to be added
     */
    public function coerce(
        Property $property,
        Constraint|array $constraints = []
    ): Result;
}
