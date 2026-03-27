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
        public string $type,
        public BagEnum $bag,
        public string|null $path = null,
        public string|null $subType = null,
        public string|null $fqcn = null,
        public bool $collection = false,
        public string|null $coercerKey = null,
        public array $constraints = [],
        public bool $requiresRuntimeResolve = false,
        public array $children = [],
        public array $meta = []
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }
}