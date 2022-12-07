<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Enum;

use DM\DtoRequestBundle\Interfaces\Enum\IntegerBackedEnumInterface;
use MyCLabs\Enum\Enum;

/**
 * @psalm-immutable
 */
class IntegerEnum extends Enum implements IntegerBackedEnumInterface
{
    public const INTEGER_KEY = 15;
    public const OTHER_KEY = 20;
    public const LAST_KEY = 25;
}
