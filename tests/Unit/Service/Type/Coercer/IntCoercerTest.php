<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[Group('coercer')]
#[CoversClass(IntCoercer::class)]
class IntCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = IntCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'int';

    protected static function getCoerceExpected(): iterable
    {
        return [
            ['null', null],
            ['0', 0],
            ['1', 1],
            [15, 15],
            [231, 231],
            ['-244', -244],
            ['-51515', -51515],
        ];
    }
}
