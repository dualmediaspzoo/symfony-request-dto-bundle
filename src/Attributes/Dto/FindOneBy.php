<?php

namespace DM\DtoRequestBundle\Attributes\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DM\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DM\DtoRequestBundle\Traits\Annotation\ProviderTrait;
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
     * @param string|null $errorPath
     * @param array<string, string> $descriptions
     * @param string|null $provider
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
        string|null $provider = null
    ) {
        $this->fields = $fields;
        $this->orderBy = $orderBy;
        $this->constraints = $constraints;
        $this->types = $types;
        $this->errorPath = $errorPath;
        $this->descriptions = $descriptions;
        $this->provider = $provider;
    }

    public function isCollection(): bool
    {
        return false;
    }
}
