<?php

namespace DualMedia\DtoRequestBundle\Traits\Annotation;

use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;

/**
 * Implements shared field/path/order fields and getters.
 */
trait FieldTrait
{
    /**
     * These fields will be later used to load the entity.
     *
     * @var array<string, string>
     */
    public array $fields = [];

    /**
     * This data will not change between requests and will be used to load the entity.
     *
     * @var array<string, mixed>
     */
    public array $static = [];

    /**
     * Order of the results.
     *
     * @var array<string, string>|null
     */
    public array|null $orderBy = null;

    /**
     * Constraints for fields used in the query so that validation can still happen.
     *
     * List of constraints mapped to final names of fields used in the query
     *
     * @var array<string, Constraint|list<Constraint>>
     */
    public array $constraints = [];

    /**
     * List of user specified type safety checks.
     *
     * @var array<string, Type>
     */
    public array $types = [];

    /**
     * Set to specify in which criteria field you want to display your error
     * Field must be specified in a {@link PropertyAccess} valid format.
     *
     * If not set the system will take the first criteria field it finds in {@link $this::$fields}
     */
    public string|null $errorPath = null;

    /**
     * List of descriptions for fields, mapped by field name, used in API docs.
     *
     * @var array<string, string>
     */
    public array $descriptions = [];

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getFields(): array
    {
        return $this->fields;
    }

    #[\Override]
    public function getFirstNonDynamicField(): string|null
    {
        foreach ($this->fields as $key => $field) {
            if (!str_starts_with($field, '$')) {
                return $field;
            }
        }

        return null;
    }

    #[\Override]
    public function getStatic(): array
    {
        return $this->static;
    }

    /**
     * @return array<string, string>|null
     */
    #[\Override]
    public function getOrderBy(): array|null
    {
        return $this->orderBy;
    }

    #[\Override]
    public function getErrorPath(): string|null
    {
        return $this->errorPath;
    }

    /**
     * @return array<string, Constraint|list<Constraint>>
     */
    #[\Override]
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return array<string, Type>
     */
    #[\Override]
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }
}
