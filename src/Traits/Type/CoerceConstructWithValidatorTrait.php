<?php

namespace DualMedia\DtoRequestBundle\Traits\Type;

use Symfony\Component\Validator\Validator\ValidatorInterface;

trait CoerceConstructWithValidatorTrait
{
    private ValidatorInterface $validator;

    public function __construct(
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }
}
