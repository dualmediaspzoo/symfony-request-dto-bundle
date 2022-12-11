<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DM\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DM\DtoRequestBundle\Traits\Annotation\LimitAndOffsetTrait;
use DM\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FindBy implements FindInterface, DtoAnnotationInterface
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
        int|null $offset = null
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
    }

    public function isCollection(): bool
    {
        return true;
    }
}
