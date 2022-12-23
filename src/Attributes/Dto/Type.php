<?php

namespace DM\DtoRequestBundle\Attributes\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;

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

            $this->type = 'object';
            $this->subType = 'string';
        } else {
            $this->type = in_array($type, ['int', 'string', 'float', 'bool']) ? $type : 'string';
            $this->subType = $subType;
        }

        $this->format = $format;
    }
}
