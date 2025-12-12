<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\CustomProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\DefaultProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\EntityHasNoProviderException;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;

/**
 * @implements ProviderServiceInterface<object>
 */
class EntityProviderService implements ProviderServiceInterface
{
    /**
     * @var array<class-string, array<string, ProviderInterface<object>>>
     */
    private array $providers = [];

    /**
     * @var array<class-string, string>
     */
    private array $defaultProviders = [];

    /**
     * @param array<string, list<array{0: ProviderInterface<object>, 1: class-string, 2: bool}>> $providers key is the service id, contains an array of ProviderInterface, FQCN and isDefault
     */
    public function __construct(
        array $providers,
        private readonly TargetProviderService|null $targetService = null
    ) {
        foreach ($providers as $id => $fields) {
            foreach ($fields as $item) {
                [$provider, $fqcn, $default] = $item;

                if (!array_key_exists($fqcn, $this->providers)) {
                    $this->providers[$fqcn] = [];
                }

                $this->providers[$fqcn][$id] = $provider;

                if ($default) {
                    $this->defaultProviders[$fqcn] = $id;
                }
            }
        }
    }

    #[\Override]
    public function getProvider(
        string $fqcn,
        string|null $providerId = null
    ): ProviderInterface {
        if (!array_key_exists($fqcn, $this->providers)) {
            if (null === $providerId && $this->targetService?->setFqcn($fqcn)) {
                return $this->targetService;
            }

            throw new EntityHasNoProviderException(sprintf(
                'No entity provider was found for model %s',
                $fqcn
            ));
        }

        $provider = null === $providerId ?
            $this->providers[$fqcn][$this->defaultProviders[$fqcn] ?? null] ?? null :
            $this->providers[$fqcn][$providerId] ?? null;

        if (null === $provider) {
            if (null === $providerId) {
                if ($this->targetService?->setFqcn($fqcn)) {
                    return $this->targetService;
                }

                throw new DefaultProviderNotFoundException(sprintf(
                    'Default provider not found for model %s',
                    $fqcn
                ));
            }

            throw new CustomProviderNotFoundException(sprintf(
                'Custom provider with id %s not found for model %s',
                $providerId,
                $fqcn
            ));
        }

        return $provider;
    }
}
