<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Enum;

enum IntegerEnum: int
{
    case INTEGER_KEY = 15;
    case OTHER_KEY = 20;
    case LAST_KEY = 25;
}
