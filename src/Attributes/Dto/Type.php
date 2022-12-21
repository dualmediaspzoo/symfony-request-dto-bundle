<?php

namespace DM\DtoRequestBundle\Attributes\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;

/**
 * This annotation provides a simple way of declaring type safety for {@link FindInterface} annotations
 */
#[\Attribute]
class Type
{
    public string $type;
    public ?string $subType;

    public function __construct(
        string $type = 'string',
        public readonly bool $collection = false,
        ?string $subType = null,
        public readonly ?Format $format = null
    ) {
        $type = mb_strtolower($type);

        if (null !== $format || in_array($type, ['date', 'datetime'])) { // fake-ish type
            $this->type = 'object';
            $this->subType = 'string';
        } else {
            $this->type = in_array($type, ['int', 'string', 'float', 'bool']) ? $type : 'string';
            $this->subType = $subType;
        }
    }
}
