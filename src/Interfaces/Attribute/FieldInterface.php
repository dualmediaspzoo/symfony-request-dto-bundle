<?php

namespace DM\DtoRequestBundle\Interfaces\Attribute;

use DM\DtoRequestBundle\Annotations\Dto\Type;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Allows an annotation to return a list of fields that were chosen to be loaded
 */
interface FieldInterface
{
    /**
     * Should be a key -> field array, dynamic parameters supported via {@link ResolverInterface} are supported
     *
     * @return array<string, string>
     */
    public function getFields(): array;

    /**
     * Returns the first field which does not start with a $ character in the field list
     *
     * @return string|null
     */
    public function getFirstNonDynamicField(): ?string;

    /**
     * Should be a key -> DESC/ASC array or null if no sorting was specified
     *
     * @return array<string, string>|null
     */
    public function getOrderBy(): ?array;

    /**
     * {@link PropertyAccess} compatible string or null (attempt to load dynamically)
     *
     * @return string|null
     */
    public function getErrorPath(): ?string;

    /**
     * List of asserts for certain fields
     *
     * @return array<string, Constraint|list<Constraint>>
     */
    public function getConstraints(): array;

    /**
     * List of user-made type safety checks
     *
     * @return array<string, Type>
     */
    public function getTypes(): array;

    /**
     * List of descriptions for fields
     *
     * Used for docs generation in Nelmio
     *
     * @return array<string, string>
     */
    public function getDescriptions(): array;
}