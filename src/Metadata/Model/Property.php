<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

readonly class Property
{
    /**
     * @param list<Constraint> $constraints
     * @param array<string, self|Dynamic|Literal> $virtual list of virtual properties existing on fields, used with FindX
     */
    public function __construct(
        public string $name,
        public Type $type,
        public BagEnum|null $bag = null,
        public string|null $path = null,
        public string|null $coercer = null,
        public array $constraints = [],
        public array $virtual = []
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }
}
