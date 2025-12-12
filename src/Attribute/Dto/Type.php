<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\FindInterface;

/**
 * This annotation provides a simple way of declaring type safety for {@link FindInterface} annotations.
 */
#[\Attribute]
class Type
{
    public readonly string $type;
    public readonly string|null $subType;

    public readonly Format|null $format;

    public function __construct(
        string $type = 'string',
        public readonly bool $collection = false,
        string|null $subType = null,
        Format|null $format = null
    ) {
        $type = mb_strtolower($type);

        if (null !== $format || in_array($type, ['date', 'datetime'], true)) { // fake-ish type
            $format ??= new Format();

            $type = 'object';
            $subType = 'string';
        } else {
            $type = in_array($type, ['int', 'string', 'float', 'bool'], true) ? $type : 'string';
        }

        $this->type = $type;
        $this->subType = $subType;
        $this->format = $format;
    }
}
