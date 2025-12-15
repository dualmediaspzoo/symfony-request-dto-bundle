<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interface\Attribute\DtoFindMetaAttributeInterface;

/**
 * Marker attribute to let the loaders know that they have to process this as a reference, by only loading the appropriate identifier.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AsDoctrineReference implements DtoAttributeInterface, DtoFindMetaAttributeInterface
{
}
