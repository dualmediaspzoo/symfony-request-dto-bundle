<?php

namespace DualMedia\DtoRequestBundle\Service\Validation;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\GroupServiceInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows for loading validation groups for dto objects.
 */
class GroupProviderService implements GroupServiceInterface
{
    /**
     * @param array<string, GroupProviderInterface> $providers
     */
    public function __construct(
        private readonly array $providers
    ) {
    }

    #[\Override]
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
