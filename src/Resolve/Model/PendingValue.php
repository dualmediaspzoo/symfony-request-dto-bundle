<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraint;

readonly class PendingValue
{
    /**
     * @param list<Constraint> $constraints type constraints from the coercer
     */
    public function __construct(
        public AbstractDto $dto,
        public string $name,
        public mixed $value,
        public array $constraints,
        public string $validationPath
    ) {
    }
}