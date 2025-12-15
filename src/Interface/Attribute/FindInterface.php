<?php

namespace DualMedia\DtoRequestBundle\Interface\Attribute;

/**
 * More direct interface for searching objects from providers.
 */
interface FindInterface extends FieldInterface, ProvidedInterface
{
    /**
     * Whether the expected result is to be a collection or not.
     */
    public function isCollection(): bool;

    /**
     * Numerical limit of the query. Return null to disable.
     */
    public function getLimit(): int|null;

    /**
     * Offset of the query. Return null to disable.
     */
    public function getOffset(): int|null;
}
