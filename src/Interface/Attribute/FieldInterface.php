<?php

namespace DualMedia\DtoRequestBundle\Interface\Attribute;

use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Interface\Dynamic\ResolverInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Allows an annotation to return a list of fields that were chosen to be loaded.
 */
interface FieldInterface
{
    /**
     * Should be a key -> field array, dynamic parameters supported via {@link ResolverInterface} are supported.
     *
     * @return array<string, string>
     */
    public function getFields(): array;

    /**
     * Returns the first field which does not start with a $ character in the field list.
     */
    public function getFirstNonDynamicField(): string|null;

    /**
     * Returns the static fields, these will not be changing between requests and are a shortcut
     * to specifying data instead of using the dynamic fields, if it's an uncommon scenario
     * or data is always known.
     *
     * @return array<string, mixed>
     */
    public function getStatic(): array;

    /**
     * Should be a key -> DESC/ASC array or null if no sorting was specified.
     *
     * @return array<string, string>|null
     */
    public function getOrderBy(): array|null;

    /**
     * {@link PropertyAccess} compatible string or null (attempt to load dynamically).
     */
    public function getErrorPath(): string|null;

    /**
     * List of asserts for certain fields.
     *
     * @return array<string, Constraint|list<Constraint>>
     */
    public function getConstraints(): array;

    /**
     * List of user-made type safety checks.
     *
     * @return array<string, Type>
     */
    public function getTypes(): array;

    /**
     * List of descriptions for fields.
     *
     * Used for docs generation in Nelmio
     *
     * @return array<string, string>
     */
    public function getDescriptions(): array;
}
