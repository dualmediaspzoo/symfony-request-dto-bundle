<?php

namespace DM\DtoRequestBundle\Service\Validation;

use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupServiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows for loading validation groups for dto objects
 */
class GroupProviderService implements GroupServiceInterface
{
    /**
     * @var array<string, GroupProviderInterface>
     */
    private array $providers;

    /**
     * @param array<string, GroupProviderInterface> $providers
     */
    public function __construct(
        array $providers
    ) {
        $this->providers = $providers;
    }

    public function provideGroups(
        Request $request,
        DtoInterface $dto,
        array $ids
    ): array {
        $groups = ['Default'];

        foreach ($ids as $id) {
            if (!array_key_exists($id, $this->providers)) {
                continue;
            }

            $groups = array_merge($groups, $this->providers[$id]->provideValidationGroups($request, $dto));
        }

        return array_values(array_unique($groups));
    }
}