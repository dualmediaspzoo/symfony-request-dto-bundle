<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Attribute;

/**
 * Allows getting the exact entity provider requested by Dto.
 */
interface ProvidedInterface
{
    /**
     * Should return a provider id, null means the default one for the object.
     */
    public function getProviderId(): string|null;
}
