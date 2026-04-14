<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Special case for overriding expected path for property/dto.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class AsRoot extends Path
{
    public function __construct()
    {
        parent::__construct('');
    }
}
