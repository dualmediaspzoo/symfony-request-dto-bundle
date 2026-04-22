<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

readonly class PendingEntityValue
{
    /**
     * @param array<string, PendingValue> $fields virtual field entries keyed by entity field target name
     * @param \Closure(array<string, mixed>): mixed $load loads the entity from criteria
     */
    public function __construct(
        public AbstractDto $dto,
        public string $name,
        public array $fields,
        public \Closure $load,
        public string $validationPath
    ) {
    }
}
