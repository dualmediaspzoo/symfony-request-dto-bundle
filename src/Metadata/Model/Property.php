<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

readonly class Property
{
    /**
     * @param list<Constraint> $constraints
     * @param array<string, Property> $children
     * @param list<mixed> $meta
     */
    public function __construct(
        public string $name,
        public Type $type,
        public BagEnum|null $bag = null,
        public string|null $path = null,
        public string|null $coercerKey = null,
        public array $constraints = [],
        public bool $requiresRuntimeResolve = false,
        public array $children = [],
        public array $meta = [],
        public bool $isDto = false
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }
}