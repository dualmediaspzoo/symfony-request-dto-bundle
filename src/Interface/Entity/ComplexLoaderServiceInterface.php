<?php

namespace DualMedia\DtoRequestBundle\Interface\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderFunctionNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\ProviderNotFoundException;
use DualMedia\DtoRequestBundle\Interface\Attribute\DtoFindMetaAttributeInterface;
use DualMedia\DtoRequestBundle\Interface\Attribute\FindComplexInterface;

/**
 * Allows loading objects via {@link FindComplexInterface}.
 */
interface ComplexLoaderServiceInterface
{
    /**
     * @template T of object
     *
     * @param class-string<T> $fqcn
     * @param array<string, mixed> $input
     * @param list<DtoFindMetaAttributeInterface> $metadata
     *
     * @throws ComplexLoaderNotFoundException
     * @throws ComplexLoaderFunctionNotFoundException
     * @throws ProviderNotFoundException
     */
    public function loadComplex(
        string $fqcn,
        FindComplexInterface $find,
        array $input,
        array $metadata = []
    ): mixed;
}
