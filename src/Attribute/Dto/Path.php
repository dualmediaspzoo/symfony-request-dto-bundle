<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\PathInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Annotation\PathTrait;

/**
 * By default, if this annotation does not exist on a property of {@link DtoInterface}, then a default object
 * with the path as the property name is created with defaults.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Path implements PathInterface, DtoAttributeInterface
{
    use PathTrait;

    public function __construct(
        string|null $path = null
    ) {
        $this->path = $path;
    }
}
