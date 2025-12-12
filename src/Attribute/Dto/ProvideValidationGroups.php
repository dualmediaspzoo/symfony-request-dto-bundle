<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\GroupProviderInterface;

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
