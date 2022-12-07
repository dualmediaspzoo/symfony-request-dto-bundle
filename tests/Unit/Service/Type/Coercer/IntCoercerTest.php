<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Service\Type\Coercer\IntCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class IntCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = IntCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'int';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['0', 0],
            ['1', 1],
            [15, 15],
            [231, 231],
            ['-244', -244],
            ['-51515', -51515],
        ];
    }
}
