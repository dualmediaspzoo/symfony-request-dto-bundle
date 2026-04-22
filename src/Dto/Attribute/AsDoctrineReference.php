<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Mark {@link FindBy} or {@link FindOneBy} with this attribute to only load a reference to the entity (lower query cost).
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AsDoctrineReference
{
}
