<?php

namespace DM\DtoRequestBundle\Service\Http;

use DM\DtoRequestBundle\Annotations\Dto\Http\OnNull;
use DM\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DM\DtoRequestBundle\Interfaces\Http\ActionValidatorInterface;

/**
 * Checks if the variable is null after loading
 */
class OnNullActionValidator implements ActionValidatorInterface
{
    public function supports(
        HttpActionInterface $action,
        $variable
    ): bool {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        return $action instanceof OnNull;
    }

    public function validate(
        HttpActionInterface $action,
        $variable
    ): bool {
        return null === $variable;
    }
}
