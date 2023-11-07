<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ComplexLoaderInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\LimitAndOffsetTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Symfony\Component\Validator\Constraint;

/**
 * @see ComplexLoaderInterface
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FindComplex implements FindComplexInterface, DtoAttributeInterface
{
    use FieldTrait;
    use ProviderTrait;
    use LimitAndOffsetTrait;

    /**
     * This string map to a method on a service listed in {@link FindComplex::$service}.
     *
     * ```php
     * callable(array $fields, ?array $orderBy, ...mixed $args): T[]|T|null
     * ```
     */
    public string|null $fn = null;

    /**
     * Service id from which the method will be called.
     *
     * The service must implement {@link ComplexLoaderInterface} to be available
     */
    public string|null $service = null;

    /**
     * Whether the complex result is expected to be a collection or not.
     */
    public bool $collection = false;

    /**
     * @param array<string, string> $fields
     * @param array<string, string>|null $orderBy
     * @param array<string, Constraint|list<Constraint>> $constraints
     * @param array<string, Type> $types
     * @param array<string, string> $descriptions
     * @param array<string, mixed> $static
     *
     * @noinspection DuplicatedCode
     */
    public function __construct(
        array $fields = [],
        array|null $orderBy = null,
        array $constraints = [],
        array $types = [],
        string|null $errorPath = null,
        array $descriptions = [],
        string|null $provider = null,
        int|null $limit = null,
        int|null $offset = null,
        string|null $fn = null,
        string|null $service = null,
        bool $collection = false,
        array $static = []
    ) {
        $this->fields = $fields;
        $this->orderBy = $orderBy;
        $this->constraints = $constraints;
        $this->types = $types;
        $this->errorPath = $errorPath;
        $this->descriptions = $descriptions;
        $this->provider = $provider;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->fn = $fn;
        $this->service = $service;
        $this->collection = $collection;
        $this->static = $static;
    }

    public function getFn(): string
    {
        if (null === $this->fn) {
            throw new \RuntimeException('No function set for callable');
        }

        return $this->fn;
    }

    public function getService(): string
    {
        if (null === $this->service) {
            throw new \RuntimeException('No service set for callable');
        }

        return $this->service;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }
}
