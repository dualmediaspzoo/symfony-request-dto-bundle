<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Http;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;

/**
 * Checks if an action is supported and should be acted upon (if nothing else is found)
 */
interface ActionValidatorInterface
{
    /**
     * @param HttpActionInterface $action
     * @param mixed $variable
     *
     * @return bool
     */
    public function supports(
        HttpActionInterface $action,
        $variable
    ): bool;

    /**
     * @param HttpActionInterface $action
     * @param mixed $variable
     *
     * @return bool
     */
    public function validate(
        HttpActionInterface $action,
        $variable
    ): bool;
}
