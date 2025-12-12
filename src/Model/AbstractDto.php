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

    private DtoInterface|null $_parentDto = null;
    private bool $_optional = true;
    private HttpActionInterface|null $_httpAction = null;

    public function __construct()
    {
        $this->_constraintList = new ConstraintViolationList();
    }

    #[\Override]
    public function getVisited(): array
    {
        return $this->_visited;
    }

    #[\Override]
    public function visited(
        string $property
    ): bool {
        return in_array($property, $this->_visited, true);
    }

    #[\Override]
    public function visitedVirtualProperty(
        string $property,
        string $field
    ): bool {
        return array_key_exists($property, $this->_visitedVirtual)
            && in_array($field, $this->_visitedVirtual[$property], true);
    }

    #[\Override]
    public function visit(
        string $property,
        string|null $virtual = null
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

    #[\Override]
    public function isOptional(): bool
    {
        return $this->_optional;
    }

    #[\Override]
    public function setOptional(
        bool $optional
    ): void {
        $this->_optional = $optional;
    }

    #[\Override]
    public function preValidate(
        string $property
    ): void {
        $this->_preValidated[] = $property;
    }

    #[\Override]
    public function isPreValidated(
        string $property
    ): bool {
        return in_array($property, $this->_preValidated);
    }

    #[\Override]
    public function addConstraintViolation(
        ConstraintViolationInterface $violation
    ): void {
        $this->_constraintList->add($violation);
    }

    /**
     * @phpstan-ignore-next-line
     */
    #[\Override]
    public function getConstraintViolationList(): ConstraintViolationListInterface
    {
        return $this->_constraintList;
    }

    #[\Override]
    public function isValid(): bool
    {
        return 0 === $this->_constraintList->count();
    }

    #[\Override]
    public function getParentDto(): DtoInterface|null
    {
        return $this->_parentDto;
    }

    #[\Override]
    public function getHighestParentDto(): DtoInterface
    {
        return $this->getParentDto()?->getHighestParentDto() ?? $this;
    }

    #[\Override]
    public function setParentDto(
        DtoInterface|null $parentDto
    ): AbstractDto {
        $this->_parentDto = $parentDto;

        return $this;
    }

    #[\Override]
    public function getHttpAction(): HttpActionInterface|null
    {
        return $this->_httpAction;
    }

    #[\Override]
    public function setHttpAction(
        HttpActionInterface|null $action
    ): void {
        $this->_httpAction = $action;
    }
}
