<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraint;

readonly class Dto
{
    /**
     * @param list<Constraint> $constraints
     * @param list<object> $meta
     */
    public function __construct(
        public string $name,
        public Type $type,
        public BagEnum|null $bag = null,
        public string|null $path = null,
        public array $constraints = [],
        public array $meta = [],
        public bool $requiresRuntimeResolve = false,
        public string|null $description = null
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }

    /**
     * @return list<string>
     */
    public function getRealPathSegments(): array
    {
        $path = $this->getRealPath();

        if ('' === $path) {
            return [];
        }

        return explode('.', $path);
    }
}
