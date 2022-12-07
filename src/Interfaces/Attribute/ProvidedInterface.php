<?php

namespace DM\DtoRequestBundle\Interfaces\Attribute;

/**
 * Allows getting the exact entity provider requested by Dto
 */
interface ProvidedInterface
{
    /**
     * Should return a provider id, null means the default one for the object
     *
     * @return string|null
     */
    public function getProviderId(): ?string;
}
