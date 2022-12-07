<?php

namespace DM\DtoRequestBundle\Interfaces\Entity;

use DM\DtoRequestBundle\Exception\Entity\ComplexLoaderFunctionNotFoundException;
use DM\DtoRequestBundle\Exception\Entity\ComplexLoaderNotFoundException;
use DM\DtoRequestBundle\Exception\Entity\ProviderNotFoundException;
use DM\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;

/**
 * Allows loading objects via {@link FindComplexInterface}
 */
interface ComplexLoaderServiceInterface
{
    /**
     * @param string $fqcn
     * @param FindComplexInterface $find
     * @param array<string, mixed> $input
     *
     * @return mixed
     *
     * @throws ComplexLoaderNotFoundException
     * @throws ComplexLoaderFunctionNotFoundException
     * @throws ProviderNotFoundException
     */
    public function loadComplex(
        string $fqcn,
        FindComplexInterface $find,
        array $input
    );
}
