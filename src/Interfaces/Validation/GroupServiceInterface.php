<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Validation;

use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows for grouping and later providing groups for dto objects
 *
 * @see GroupProviderInterface
 */
interface GroupServiceInterface
{
    /**
     * Returns a list of groups to be provided for a dto
     *
     * This method is always called once per dto, only the parent dto will have its groups checked
     *
     * @param Request $request
     * @param DtoInterface $dto
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
