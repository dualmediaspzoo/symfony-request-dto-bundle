<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection\Interface;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;

interface MainDtoMemoizerInterface
{
    /**
     * @param class-string<AbstractDto> $class
     */
    public function get(
        string $class
    ): MainDto|null;
}
