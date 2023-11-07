<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Attribute;

/**
 * More direct interface for searching objects from providers.
 */
interface FindInterface extends FieldInterface, ProvidedInterface
{
    /**
     * Whether the expected result is to be a collection or not.
     */
    public function isCollection(): bool;
}
