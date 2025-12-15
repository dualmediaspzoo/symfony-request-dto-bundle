<?php

namespace DualMedia\DtoRequestBundle\Service\Http;

use DualMedia\DtoRequestBundle\Attribute\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interface\Http\ActionValidatorInterface;

/**
 * Checks if the variable is null after loading.
 */
class OnNullActionValidator implements ActionValidatorInterface
{
    #[\Override]
    public function supports(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $action instanceof OnNull;
    }

    #[\Override]
    public function validate(
        HttpActionInterface $action,
        mixed $variable
    ): bool {
        return null === $variable;
    }
}
