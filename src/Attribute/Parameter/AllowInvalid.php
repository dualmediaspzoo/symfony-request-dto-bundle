<?php

namespace DualMedia\DtoRequestBundle\Attribute\Parameter;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;

/**
 * When used with {@link DtoInterface} objects as an input to a controller no error will be triggered.
 */
#[\Attribute(\Attribute::TARGET_PARAMETER)]
class AllowInvalid
{
}
