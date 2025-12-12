<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FindOneBy implements FindInterface, DtoAttributeInterface
{
    use FieldTrait;
    use ProviderTrait;

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
        array $static = []
    ) {
        $this->fields = $fields;
        $this->orderBy = $orderBy;
        $this->constraints = $constraints;
        $this->types = $types;
        $this->errorPath = $errorPath;
        $this->descriptions = $descriptions;
        $this->provider = $provider;
        $this->static = $static;
    }

    public function isCollection(): bool
    {
        return false;
    }
}
