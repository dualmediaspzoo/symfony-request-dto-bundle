<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Marker property for input. Expected on singular objects/entities.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class FindOneBy
{
}
