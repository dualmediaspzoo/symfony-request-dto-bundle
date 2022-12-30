<?php

namespace DualMedia\DtoRequestBundle\Interfaces;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface DtoInterface
{
    /**
     * The constructor for Dto models must not have any required arguments
     */
    public function __construct();

    /**
     * Returns list of non-virtual visited properties
     *
     * @return list<string>
     */
    public function getVisited(): array;

    /**
     * Returns true if a field was accessed during initialization
     *
     * @param string $property
     *
     * @return bool
     */
    public function visited(
        string $property
    ): bool;

    /**
     * Returns true if a virtual field was visited during load (only via {@link FindInterface})
     *
     * @param string $property
     * @param string $field
     *
     * @return bool
     */
    public function visitedVirtualProperty(
        string $property,
        string $field
    ): bool;

    /**
     * Mark property as visited
     *
     * @param string $property
     * @param string|null $virtual
     *
     * @return void
     */
    public function visit(
        string $property,
        ?string $virtual = null
    ): void;

    /**
     * Returns true if the dto object was an argument that was optional (nullable) or not the only type
     *
     * @return bool
     */
    public function isOptional(): bool;

    /**
     * Sets the optional state
     *
     * @param bool $optional
     *
     * @return void
     */
    public function setOptional(
        bool $optional
    ): void;

    /**
     * Marks the property as pre-validated by the dto resolver
     *
     * Allows use of {@link AfterLoad} constraint on property
     *
     * @param string $property
     *
     * @return void
     *
     * @internal
     */
    public function preValidate(
        string $property
    ): void;

    /**
     * Checks if the property was pre-validated by the dto resolver
     *
     * Will trigger any {@link AfterLoad} constraints
     *
     * @param string $property
     *
     * @return bool
     *
     * @internal
     */
    public function isPreValidated(
        string $property
    ): bool;

    /**
     * @param ConstraintViolationInterface $violation
     *
     * @return void
     */
    public function addConstraintViolation(
        ConstraintViolationInterface $violation
    ): void;

    /**
     * @phpstan-ignore-next-line
     * @return ConstraintViolationListInterface
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface;

    /**
     * Checks if the DTO is valid
     *
     * Should return true only if the constraint list has been set and no issues have been found
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get the parent dto
     *
     * Null is returned if there is no parent
     *
     * @return DtoInterface|null
     */
    public function getParentDto(): ?DtoInterface;

    /**
     * Get the highest dto (usually for error adding)
     *
     * Method always returns either parents, or this object
     *
     * @return DtoInterface
     */
    public function getHighestParentDto(): DtoInterface;

    /**
     * Set parent dto
     *
     * @param DtoInterface|null $parentDto
     *
     * @return mixed
     */
    public function setParentDto(
        ?DtoInterface $parentDto
    ): mixed;

    /**
     * @param HttpActionInterface|null $action
     *
     * @return void
     */
    public function setHttpAction(
        ?HttpActionInterface $action
    ): void;

    /**
     * @return HttpActionInterface|null
     */
    public function getHttpAction(): ?HttpActionInterface;
}
