<?php

namespace DualMedia\DtoRequestBundle\Traits\Annotation;

trait ProviderTrait
{
    /**
     * Specifies which class should be used to provide the object
     *
     * Leave as null to use the default provider, otherwise specify the id or FQCN
     */
    public string|null $provider = null;

    public function getProviderId(): string|null
    {
        return $this->provider;
    }
}
