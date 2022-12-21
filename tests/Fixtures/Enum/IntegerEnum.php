<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Enum;

/**
 * @psalm-immutable
 */
enum IntegerEnum: int
{
    public const INTEGER_KEY = 15;
    public const OTHER_KEY = 20;
    public const LAST_KEY = 25;
}
