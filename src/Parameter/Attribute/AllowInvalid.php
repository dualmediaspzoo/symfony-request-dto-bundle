<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Parameter\Attribute;

/**
 * Allows an invalid dto to pass through the value provider and into the controller.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
readonly class AllowInvalid
{
}
