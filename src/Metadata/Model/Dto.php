<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

readonly class Dto
{
    /**
     * @param list<Constraint> $constraints
     */
    public function __construct(
        public string $name,
        public Type $type,
        public BagEnum|null $bag = null,
        public string|null $path = null,
        public array $constraints = []
    ) {
    }

    public function getRealPath(): string
    {
        return $this->path ?? $this->name;
    }
}
