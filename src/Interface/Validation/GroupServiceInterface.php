<?php

namespace DualMedia\DtoRequestBundle\Interface\Validation;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows for grouping and later providing groups for dto objects.
 *
 * @see GroupProviderInterface
 */
interface GroupServiceInterface
{
    /**
     * Returns a list of groups to be provided for a dto.
     *
     * This method is always called once per dto, only the parent dto will have its groups checked
     *
     * @param list<string> $ids
     *
     * @return list<string>
     */
    public function provideGroups(
        Request $request,
        DtoInterface $dto,
        array $ids
    ): array;
}
