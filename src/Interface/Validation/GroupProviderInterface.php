<?php

namespace DualMedia\DtoRequestBundle\Interface\Validation;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Allows custom validation groups to be used with a dto.
 */
interface GroupProviderInterface
{
    /**
     * Must provide validation groups based on some sort of input.
     *
     * @return list<string>
     */
    public function provideValidationGroups(
        Request $request,
        DtoInterface $dto
    ): array;
}
