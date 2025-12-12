<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderFunctionNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\ComplexLoaderNotFoundException;
use DualMedia\DtoRequestBundle\Interface\Attribute\FindComplexInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ComplexLoaderInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ComplexLoaderServiceInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ProviderServiceInterface;

class ComplexLoaderService implements ComplexLoaderServiceInterface
{
    /**
     * @param array<string, ComplexLoaderInterface> $loaders
     * @param ProviderServiceInterface<object> $providerService
     */
    public function __construct(
        private readonly array $loaders,
        private readonly ProviderServiceInterface $providerService
    ) {
    }

    #[\Override]
    public function loadComplex(
        string $fqcn,
        FindComplexInterface $find,
        array $input,
        array $metadata = []
    ): mixed {
        if (!array_key_exists($find->getService(), $this->loaders)) {
            throw new ComplexLoaderNotFoundException(sprintf(
                'Attempted to use loader with id %s',
                $find->getService()
            ));
        }

        if (!method_exists($this->loaders[$find->getService()], $find->getFn())) {
            throw new ComplexLoaderFunctionNotFoundException(sprintf(
                'Method %s does not exist on class %s',
                $find->getFn(),
                get_class($this->loaders[$find->getService()]),
            ));
        }

        return $this->providerService->getProvider(
            $fqcn,
            $find->getProviderId()
        )->findComplex(
            \Closure::fromCallable([$this->loaders[$find->getService()], $find->getFn()]), // @phpstan-ignore-line
            $input,
            $find->getOrderBy(),
            $metadata
        );
    }
}
