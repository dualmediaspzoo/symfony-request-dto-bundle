<?php

namespace DualMedia\DtoRequestBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Makes sure all elements are an array
 */
#[\Attribute]
class ArrayAll extends Constraint
{
    public const NOT_ALL_ELEMENTS_ARE_ARRAY_ERROR = 'ac4b2cd2-0cb5-4ea9-b5ed-c0e274f09658';

    public string $message = 'This field must be an array of arrays.';
}
