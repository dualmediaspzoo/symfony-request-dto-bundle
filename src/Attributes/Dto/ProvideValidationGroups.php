<?php

namespace DM\DtoRequestBundle\Attributes\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;

/**
 * This annotation should be put on {@link DtoInterface} objects, which wish to specify what groups they'll use
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
