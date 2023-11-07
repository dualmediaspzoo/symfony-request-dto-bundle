<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Attribute;

/**
 * Same as {@link FindInterface}, but has a callback method.
 */
interface FindComplexInterface extends FindInterface
{
    /**
     * Gets method used for loading the object.
     */
    public function getFn(): string;

    /**
     * Gets service used for loading the object.
     */
    public function getService(): string;
}
