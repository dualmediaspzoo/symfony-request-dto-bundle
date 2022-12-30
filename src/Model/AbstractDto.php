<?php

namespace DualMedia\DtoRequestBundle\Model;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractDto implements DtoInterface
{
    /**
     * @phpstan-ignore-next-line
     */
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

    private ?DtoInterface $_parentDto = null;
    private bool $_optional = true;
    private ?HttpActionInterface $_httpAction = null;

    public function __construct()
    {
        $this->_constraintList = new ConstraintViolationList();
    }

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
        return array_key_exists($property, $this->_visitedVirtual) &&
            in_array($field, $this->_visitedVirtual[$property], true);
    }

    public function visit(
        string $property,
        ?string $virtual = null
    ): void {
        if (null !== $virtual) {
            if (!array_key_exists($property, $this->_visitedVirtual)) {
                $this->_visitedVirtual[$property] = [];
            }

            $this->_visitedVirtual[$property][] = $virtual;
        } else {
            $this->_visited[] = $property;
        }
    }

    public function isOptional(): bool
    {
        return $this->_optional;
    }

    public function setOptional(
        bool $optional
    ): void {
        $this->_optional = $optional;
    }

    public function preValidate(
        string $property
    ): void {
        $this->_preValidated[] = $property;
    }

    public function isPreValidated(
        string $property
    ): bool {
        return in_array($property, $this->_preValidated);
    }

    public function addConstraintViolation(
        ConstraintViolationInterface $violation
    ): void {
        $this->_constraintList->add($violation);
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->_constraintList;
    }

    public function isValid(): bool
    {
        return 0 === $this->_constraintList->count();
    }

    public function getParentDto(): ?DtoInterface
    {
        return $this->_parentDto;
    }

    public function getHighestParentDto(): DtoInterface
    {
        return $this->getParentDto()?->getHighestParentDto() ?? $this;
    }

    public function setParentDto(
        ?DtoInterface $parentDto
    ): AbstractDto {
        $this->_parentDto = $parentDto;

        return $this;
    }

    public function getHttpAction(): ?HttpActionInterface
    {
        return $this->_httpAction;
    }

    public function setHttpAction(
        ?HttpActionInterface $action
    ): void {
        $this->_httpAction = $action;
    }
}
