<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * Marker property for input. Expected on multiple objects/entities.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class FindBy
{
}
