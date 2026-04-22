<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Enum;

enum IntBackedEnum: int
{
    case One = 1;
    case Two = 2;
}
