<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Enum;

enum StringEnum: string
{
    case StringKey = 'not_string_key';

    case SecondStringKey = 'other_string_value';
}
