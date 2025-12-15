<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interface\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\LimitAndOffsetTrait;
use DualMedia\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FindOneBy implements FindInterface, DtoAttributeInterface
{
    use FieldTrait;
    use ProviderTrait;
    use LimitAndOffsetTrait;

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
        $this->offset = $offset;
        $this->static = $static;
    }

    #[\Override]
    public function isCollection(): bool
    {
        return false;
    }
}
