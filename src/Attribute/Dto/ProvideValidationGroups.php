<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;

/**
 * This annotation should be put on {@link DtoInterface} objects, which wish to specify what groups they'll use.
 *
 * Services that will determine this, must implement {@link GroupProviderInterface}
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ProvideValidationGroups implements DtoAttributeInterface
{
    public function __construct(
        public readonly string $provider
    ) {
    }
}
