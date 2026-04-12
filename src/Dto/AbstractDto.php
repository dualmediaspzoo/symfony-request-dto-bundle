<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto;

use DualMedia\DtoRequestBundle\Dto\Interface\DtoInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class AbstractDto implements DtoInterface
{
    private ConstraintViolationListInterface $_constraintList;

    /**
     * @var list<string>
     */
    private array $_visited = [];

    /**
     * @var array<string, list<string>>
     */
    private array $_visitedVirtual = [];

    /**
     * @var list<string>
     */
    private array $_preValidated = [];

    private AbstractDto|null $_parentDto = null;

    private bool $optional = false;

    public function __construct()
    {
        $this->_constraintList = new ConstraintViolationList();
    }

    /**
     * @return list<string>
     */
    public function getVisited(): array
    {
        return $this->_visited;
    }

    public function visited(
        string $property
    ): bool {
        return in_array($property, $this->_visited, true);
    }

    public function visitedVirtualProperty(
        string $property,
        string $field
    ): bool {
        return array_key_exists($property, $this->_visitedVirtual)
            && in_array($field, $this->_visitedVirtual[$property], true);
    }

    /**
     * @internal
     */
    public function visit(
        string $property,
        string|null $virtual = null
    ): void {
        if (null === $virtual) {
            $this->_visited[] = $property;

            return;
        }

        if (!array_key_exists($property, $this->_visitedVirtual)) {
            $this->_visitedVirtual[$property] = [];
        }

        $this->_visitedVirtual[$property][] = $virtual;
    }

    /**
     * @internal
     */
    public function preValidate(
        string $property
    ): void {
        $this->_preValidated[] = $property;
    }

    /**
     * @internal
     */
    public function isPreValidated(
        string $property
    ): bool {
        return in_array($property, $this->_preValidated, true);
    }

    /**
     * @internal
     */
    public function addConstraintViolation(
        ConstraintViolationInterface $violation
    ): void {
        $this->_constraintList->add($violation);
    }

    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->_constraintList;
    }

    public function isValid(): bool
    {
        return 0 === $this->_constraintList->count();
    }

    public function getParentDto(): AbstractDto|null
    {
        return $this->_parentDto;
    }

    public function getHighestParentDto(): AbstractDto
    {
        return $this->getParentDto()?->getHighestParentDto() ?? $this;
    }

    public function isOptional(): bool
    {
        return $this->optional;
    }

    /**
     * @internal
     */
    public function setOptional(
        bool $optional
    ): void {
        $this->optional = $optional;
    }

    /**
     * @internal
     */
    public function setParentDto(
        AbstractDto|null $parentDto
    ): void {
        $this->_parentDto = $parentDto;
    }
}
