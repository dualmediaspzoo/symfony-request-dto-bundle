<?php

namespace DM\DtoRequestBundle\Interfaces\Attribute;

/**
 * Same as {@link FindInterface}, but has a callback method
 */
interface FindComplexInterface extends FindInterface
{
    /**
     * Gets method used for loading the object
     *
     * @return string
     */
    public function getFn(): string;

    /**
     * Gets service used for loading the object
     *
     * @return string
     */
    public function getService(): string;
}
