<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\PathInterface;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Traits\Annotation\PathTrait;

/**
 * By default, if this annotation does not exist on a property of {@link DtoInterface}, then a default object
 * with the path as the property name is created with defaults
 *
 * @Annotation
 * @Target("PROPERTY")
 * @NamedArgumentConstructor()
 */
class Path implements PathInterface, DtoAnnotationInterface
{
    use PathTrait;

    public function __construct(
        ?string $path = null
    ) {
        $this->path = $path;
    }
}
