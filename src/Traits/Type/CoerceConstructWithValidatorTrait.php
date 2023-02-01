<?php

namespace DualMedia\DtoRequestBundle\Traits\Type;

use Symfony\Component\Validator\Validator\ValidatorInterface;

trait CoerceConstructWithValidatorTrait
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {
    }
}
