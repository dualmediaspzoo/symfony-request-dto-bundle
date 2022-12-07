<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\Target;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DM\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DM\DtoRequestBundle\Traits\Annotation\ProviderTrait;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class FindOneBy implements FindInterface, DtoAnnotationInterface
{
    use FieldTrait;
    use ProviderTrait;

    public function isCollection(): bool
    {
        return false;
    }
}
