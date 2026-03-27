<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class ObjectType extends Type
{
    /**
     * @param class-string $fqcn
     */
    public function __construct(
        string $fqcn
    ) {
        parent::__construct('object', $fqcn);
    }
}
