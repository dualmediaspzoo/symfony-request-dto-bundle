<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\LimitAndOffsetTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FindBy implements FindInterface, DtoAttributeInterface
{
    use FieldTrait;
    use ProviderTrait;
    use LimitAndOffsetTrait;

    /**
     * @param array<string, string> $fields
     * @param array<string, string>|null $orderBy
     * @param array<string, Constraint|list<Constraint>> $constraints
     * @param array<string, Type> $types
     * @param string|null $errorPath
     * @param array<string, string> $descriptions
     * @param string|null $provider
     * @param int|null $limit
     * @param int|null $offset
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
        $this->static = $static;
    }

    public function isCollection(): bool
    {
        return true;
    }
}
