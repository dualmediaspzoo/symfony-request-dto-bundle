<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Enum;

use MyCLabs\Enum\Enum;

/**
 * @psalm-immutable
 */
class StringEnum extends Enum
{
    public const STRING_KEY = 'not_string_key';
}
