<?php

namespace DM\DtoRequestBundle\Traits\Annotation;

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Implements shared path fields
 */
trait PathTrait
{
    /**
     * Path for the item
     *
     * Default means just the object property name
     * Value must be compatible with the {@link PropertyAccess} syntax for objects
     * Path must only take into account this object, as there might be a parent path inherited
     */
    public ?string $path = null;

    /**
     * {@inheritdoc}
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}
