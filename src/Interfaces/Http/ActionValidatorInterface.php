<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Http;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;

/**
 * Checks if an action is supported and should be acted upon (if nothing else is found).
 */
interface ActionValidatorInterface
{
    public function supports(
        HttpActionInterface $action,
        mixed $variable
    ): bool;

    public function validate(
        HttpActionInterface $action,
        mixed $variable
    ): bool;
}
