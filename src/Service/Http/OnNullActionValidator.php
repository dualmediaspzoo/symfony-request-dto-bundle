<?php

namespace DualMedia\DtoRequestBundle\Service\Http;

use DualMedia\DtoRequestBundle\Attribute\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interfaces\Http\ActionValidatorInterface;

/**
 * Checks if the variable is null after loading.
 */
class OnNullActionValidator implements ActionValidatorInterface
{
    public function supports(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $action instanceof OnNull;
    }

    public function validate(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        return null === $variable;
    }
}
