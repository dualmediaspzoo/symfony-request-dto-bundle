<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Attribute;

interface PathInterface
{
    /**
     * Path for the item.
     *
     * Null means the property name
     * Value must be compatible with the {@link PropertyAccess} syntax for arrays
     * Path must only take into account this object, as there might be a parent path inherited
     */
    public function getPath(): string|null;
}
