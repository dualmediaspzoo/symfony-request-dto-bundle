<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DM\DtoRequestBundle\Traits\Annotation\FieldTrait;
use DM\DtoRequestBundle\Traits\Annotation\ProviderTrait;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class FindBy implements FindInterface, DtoAnnotationInterface
{
    use FieldTrait;
    use ProviderTrait;

    /**
     * Result limit
     *
     * @var int|null
     */
    public ?int $limit = null;

    /**
     * Result offset
     *
     * @var int|null
     */
    public ?int $offset = null;

    public function isCollection(): bool
    {
        return true;
    }
}
