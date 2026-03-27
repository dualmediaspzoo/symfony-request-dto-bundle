<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Builder;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;
use Symfony\Component\Validator\Constraint;

class PropertyBuilder
{
    private string|null $path = null;
    private string|null $coercerKey = null;
    private bool $requiresRuntimeResolve = false;

    /** @var list<Constraint> */
    private array $constraints = [];

    /** @var array<string, Property> */
    private array $children = [];

    /** @var list<mixed> */
    private array $meta = [];

    public function __construct(
        private readonly string $name,
        private readonly Type $type,
        private readonly BagEnum|null $bag = null
    ) {
    }

    public function path(
        string $path
    ): static {
        $this->path = $path;

        return $this;
    }

    public function coercerKey(
        string $coercerKey
    ): static {
        $this->coercerKey = $coercerKey;

        return $this;
    }

    public function requiresRuntimeResolve(
        bool $requiresRuntimeResolve = true
    ): static {
        $this->requiresRuntimeResolve = $requiresRuntimeResolve;

        return $this;
    }

    public function constraint(
        Constraint $constraint
    ): static {
        $this->constraints[] = $constraint;

        return $this;
    }

    /**
     * @param list<Constraint> $constraints
     */
    public function constraints(
        array $constraints
    ): static {
        $this->constraints = [...$this->constraints, ...$constraints];

        return $this;
    }

    public function child(
        Property $child
    ): static {
        $this->children[$child->name] = $child;

        return $this;
    }

    /**
     * @param array<string, Property> $children
     */
    public function children(
        array $children
    ): static {
        $this->children = [...$this->children, ...$children];

        return $this;
    }

    public function meta(
        mixed $item
    ): static {
        $this->meta[] = $item;

        return $this;
    }

    public function build(): Property
    {
        return new Property(
            name: $this->name,
            type: $this->type,
            bag: $this->bag,
            path: $this->path,
            coercerKey: $this->coercerKey,
            constraints: $this->constraints,
            requiresRuntimeResolve: $this->requiresRuntimeResolve,
            children: $this->children,
            meta: $this->meta,
        );
    }
}
