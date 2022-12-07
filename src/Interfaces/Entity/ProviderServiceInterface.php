<?php

namespace DM\DtoRequestBundle\Interfaces\Entity;

use DM\DtoRequestBundle\Exception\Entity\CustomProviderNotFoundException;
use DM\DtoRequestBundle\Exception\Entity\DefaultProviderNotFoundException;
use DM\DtoRequestBundle\Exception\Entity\EntityHasNoProviderException;

/**
 * Allows fetching {@link ProviderInterface} objects from a global storage via FQCN and optionally {@link ProviderInterface} id
 */
interface ProviderServiceInterface
{
    /**
     * @template T of string
     *
     * @param T $fqcn
     * @param string|null $providerId
     *
     * @return ProviderInterface<T>
     *
     * @throws EntityHasNoProviderException when fqcn doesn't match any known providers
     * @throws DefaultProviderNotFoundException
     * @throws CustomProviderNotFoundException only thrown if $providerId was specified
     */
    public function getProvider(
        string $fqcn,
        ?string $providerId = null
    ): ProviderInterface;
}
