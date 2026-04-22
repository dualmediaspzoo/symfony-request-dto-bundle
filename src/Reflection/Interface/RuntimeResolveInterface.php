<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection\Interface;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;

interface RuntimeResolveInterface
{
    public function prepareForCache(
        MainDto $mainDto
    ): MainDto;

    /**
     * @param class-string<AbstractDto> $class
     */
    public function restoreRuntimeConstraints(
        string $class,
        MainDto $mainDto
    ): MainDto;
}
