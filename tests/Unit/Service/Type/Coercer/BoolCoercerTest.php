<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class BoolCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = BoolCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'bool';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['null', null],
            ['0', false],
            ['1', true],
            [0, false],
            [1, true],
            [false, false],
            [true, true],
        ];
    }
}
