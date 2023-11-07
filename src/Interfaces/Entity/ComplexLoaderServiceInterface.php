<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderFunctionNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\ProviderNotFoundException;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;

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
     *
     * @throws ComplexLoaderNotFoundException
     * @throws ComplexLoaderFunctionNotFoundException
     * @throws ProviderNotFoundException
     */
    public function loadComplex(
        string $fqcn,
        FindComplexInterface $find,
        array $input
    ): mixed;
}
