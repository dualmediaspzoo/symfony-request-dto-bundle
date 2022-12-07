<?php

namespace DM\DtoRequestBundle\Interfaces\Validation;

use DM\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows custom validation groups to be used with a dto
 */
interface GroupProviderInterface
{
    /**
     * Must provide validation groups based on some sort of input
     *
     * @param Request $request
     * @param DtoInterface $dto
     *
     * @return list<string>
     */
    public function provideValidationGroups(
        Request $request,
        DtoInterface $dto
    ): array;
}
