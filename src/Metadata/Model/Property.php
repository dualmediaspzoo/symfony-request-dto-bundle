<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraint;

readonly class Property
{
    /**
     * @param list<Constraint> $constraints
     * @param array<string, self|Dynamic|Literal> $virtual list of virtual properties existing on fields, used with FindX
     * @param list<object> $meta list of special fields to be saved and read later
     */
    public function __construct(
        public string $name,
        public Type $type,
        public BagEnum|null $bag = null,
        public string|null $path = null,
        public string|null $coercer = null,
        public array $constraints = [],
        public array $virtual = [],
        public array $meta = [],
        public string|null $objectProviderServiceId = null,
        public bool $requiresRuntimeResolve = false,
        public string|null $description = null
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }

    /**
     * Splits the real path on `.` so dotted `Path('inner.description')`
     * resolves as multiple request-bag segments.
     *
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
