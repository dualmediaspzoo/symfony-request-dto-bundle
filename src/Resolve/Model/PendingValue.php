<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraint;

readonly class PendingValue
{
    /**
     * @param list<array{mixed, list<Constraint>}> $phases validation phases, evaluated in order; stops on first failure
     */
    public function __construct(
        public AbstractDto $dto,
        public string $name,
        public mixed $value,
        public array $phases,
        public string $validationPath
    ) {
    }
}
