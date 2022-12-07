<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;

/**
 * @Annotation
 * @NamedArgumentConstructor()
 */
class AllowEnum implements DtoAnnotationInterface
{
    /**
     * Allowed enum values
     *
     * Use enum keys
     *
     * @var array
     * @psalm-var list<string>
     */
    public array $allowed = [];

    /**
     * @param list<string> $allowed
     */
    public function __construct(
        array $allowed = []
    ) {
        $this->allowed = $allowed;
    }
}
