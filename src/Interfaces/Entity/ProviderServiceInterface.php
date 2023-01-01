<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\CustomProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\DefaultProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\EntityHasNoProviderException;

/**
 * Allows fetching {@link ProviderInterface} objects from a global storage via FQCN and optionally {@link ProviderInterface} id
 *
 * @template T of object
 */
interface ProviderServiceInterface
{
    /**
     * @param class-string<T> $fqcn
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
