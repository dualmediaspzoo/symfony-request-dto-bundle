<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Enum;

enum StringBackedEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}
