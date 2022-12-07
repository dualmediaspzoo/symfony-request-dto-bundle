<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ComplexLoaderInterface;
use DM\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DM\DtoRequestBundle\Traits\Annotation\ProviderTrait;

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @see ComplexLoaderInterface
 */
class FindComplex implements FindComplexInterface, DtoAnnotationInterface
{
    use FieldTrait;
    use ProviderTrait;

    /**
     * This string map to a method on a service listed in {@link FindComplex::$service}
     *
     * ```php
     * callable(array $fields, ?array $orderBy, ...mixed $args): T[]|T|null
     * ```
     */
    public ?string $fn = null;

    /**
     * Service id from which the method will be called
     *
     * The service must implement {@link ComplexLoaderInterface} to be available
     */
    public ?string $service = null;

    /**
     * Whether the complex result is expected to be a collection or not
     *
     * @var bool
     */
    public bool $collection = false;

    public function getFn(): string
    {
        if (null === $this->fn) {
            throw new \RuntimeException("No function set for callable");
        }

        return $this->fn;
    }

    public function getService(): string
    {
        if (null === $this->service) {
            throw new \RuntimeException("No service set for callable");
        }

        return $this->service;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }
}
