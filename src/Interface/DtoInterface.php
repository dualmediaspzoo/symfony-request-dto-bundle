<?php

namespace DualMedia\DtoRequestBundle\Interface;

use DualMedia\DtoRequestBundle\Interface\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface DtoInterface
{
    /**
     * The constructor for Dto models must not have any required arguments.
     */
    public function __construct();

    /**
     * Returns list of non-virtual visited properties.
     *
     * @return list<string>
     */
    public function getVisited(): array;

    /**
     * Returns true if a field was accessed during initialization.
     */
    public function visited(
        string $property
    ): bool;

    /**
     * Returns true if a virtual field was visited during load (only via {@link FindInterface}).
     */
    public function visitedVirtualProperty(
        string $property,
        string $field
    ): bool;

    /**
     * Mark property as visited.
     */
    public function visit(
        string $property,
        string|null $virtual = null
    ): void;

    /**
     * Returns true if the dto object was an argument that was optional (nullable) or not the only type.
     */
    public function isOptional(): bool;

    /**
     * Sets the optional state.
     */
    public function setOptional(
        bool $optional
    ): void;

    /**
     * Marks the property as pre-validated by the dto resolver.
     *
     * Allows use of {@link AfterLoad} constraint on property
     *
     * @internal
     */
    public function preValidate(
        string $property
    ): void;

    /**
     * Checks if the property was pre-validated by the dto resolver.
     *
     * Will trigger any {@link AfterLoad} constraints
     *
     * @internal
     */
    public function isPreValidated(
        string $property
    ): bool;

    public function addConstraintViolation(
        ConstraintViolationInterface $violation
    ): void;

    /**
     * @phpstan-ignore-next-line
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface;

    /**
     * Checks if the DTO is valid.
     *
     * Should return true only if the constraint list has been set and no issues have been found
     */
    public function isValid(): bool;

    /**
     * Get the parent dto.
     *
     * Null is returned if there is no parent
     */
    public function getParentDto(): DtoInterface|null;

    /**
     * Get the highest dto (usually for error adding).
     *
     * Method always returns either parents, or this object
     */
    public function getHighestParentDto(): DtoInterface;

    /**
     * Set parent dto.
     */
    public function setParentDto(
        DtoInterface|null $parentDto
    ): mixed;

    public function setHttpAction(
        HttpActionInterface|null $action
    ): void;

    public function getHttpAction(): HttpActionInterface|null;
}
