<?php

namespace DM\DtoRequestBundle\Traits\Annotation;

use DM\DtoRequestBundle\Annotations\Dto\Type;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;

/**
 * Implements shared field/path/order fields and getters
 */
trait FieldTrait
{
    /**
     * These fields will be later used to load the entity
     *
     * @var array
     * @psalm-var array<string, string>
     */
    public array $fields = [];

    /**
     * Order of the results
     *
     * @var array
     * @psalm-var array<string, string>|null
     */
    public ?array $orderBy = null;

    /**
     * Constraints for fields used in the query so that validation can still happen
     *
     * List of constraints mapped to final names of fields used in the query
     *
     * @var array
     * @psalm-var array<string, Constraint|list<Constraint>>
     */
    public array $constraints = [];

    /**
     * List of user specified type safety checks
     *
     * @var array
     * @psalm-var array<string, Type>
     */
    public array $types = [];

    /**
     * Set to specify in which criteria field you want to display your error
     * Field must be specified in a {@link PropertyAccess} valid format
     *
     * If not set the system will take the first criteria field it finds in {@link $this::$fields}
     */
    public ?string $errorPath = null;

    /**
     * List of descriptions for fields, mapped by field name
     *
     * @var array
     * @psalm-var array<string, string>
     */
    public array $descriptions = [];

    /**
     * @return array<string, string>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getFirstNonDynamicField(): ?string
    {
        foreach ($this->fields as $key => $field) {
            if (!str_starts_with($field, '$')) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    public function getOrderBy(): ?array
    {
        return $this->orderBy;
    }

    public function getErrorPath(): ?string
    {
        return $this->errorPath;
    }

    /**
     * @return array<string, Constraint|list<Constraint>>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return array<string, Type>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @return array<string, string>
     */
    public function getDescriptions(): array
    {
        return $this->descriptions;
    }
}
