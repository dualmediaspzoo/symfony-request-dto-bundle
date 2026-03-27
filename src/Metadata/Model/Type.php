<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Type
{
    /**
     * @param string|null $collection type of collection (array|Collection::Class)
     */
    public function __construct(
        public string $type,
        public string|null $collection,
        public string|null $fqcn = null,
        public string|null $subType = null
    ) {
    }

    public function isCollection(): bool
    {
        return null !== $this->collection;
    }
}
