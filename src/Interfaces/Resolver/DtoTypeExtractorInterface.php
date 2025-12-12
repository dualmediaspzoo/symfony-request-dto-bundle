<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Resolver;

use DualMedia\DtoRequestBundle\Attribute\Dto\Bag;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidDateTimeClassException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Model\Type\Dto;

interface DtoTypeExtractorInterface
{
    /**
     * @param \ReflectionClass<DtoInterface> $class
     *
     * @throws InvalidTypeCountException
     * @throws InvalidDateTimeClassException
     */
    public function extract(
        \ReflectionClass $class,
        Bag|null $root = null
    ): Dto;
}
