<?php

namespace DM\DtoRequestBundle\Interfaces\Resolver;

use DM\DtoRequestBundle\Attributes\Dto\Bag;
use DM\DtoRequestBundle\Exception\Type\InvalidDateTimeClassException;
use DM\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Model\Type\Dto;

interface DtoTypeExtractorInterface
{
    /**
     * @param \ReflectionClass<DtoInterface> $class
     * @param Bag|null $root
     *
     * @return Dto
     *
     * @throws InvalidTypeCountException
     * @throws InvalidDateTimeClassException
     */
    public function extract(
        \ReflectionClass $class,
        ?Bag $root = null
    ): Dto;
}
