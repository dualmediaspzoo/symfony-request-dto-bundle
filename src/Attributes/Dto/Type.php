<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;

/**
 * This annotation provides a simple way of declaring type safety for {@link FindInterface} annotations
 */
#[\Attribute]
class Type
{
    public readonly string $type;
    public readonly ?string $subType;

    public readonly ?Format $format;

    public function __construct(
        string $type = 'string',
        public readonly bool $collection = false,
        ?string $subType = null,
        ?Format $format = null
    ) {
        $type = mb_strtolower($type);

        if (null !== $format || in_array($type, ['date', 'datetime'])) { // fake-ish type
            $format ??= new Format();

            $type = 'object';
            $subType = 'string';
        } else {
            $type = in_array($type, ['int', 'string', 'float', 'bool']) ? $type : 'string';
        }

        $this->type = $type;
        $this->subType = $subType;
        $this->format = $format;
    }
}
