<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Match enum cases by name instead of backed value.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class FromKey
{
}
