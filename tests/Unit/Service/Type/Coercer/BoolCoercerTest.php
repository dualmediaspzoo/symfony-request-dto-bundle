<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Service\Type\Coercer\BoolCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class BoolCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = BoolCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'bool';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['0', false],
            ['1', true],
            [0, false],
            [1, true],
            [false, false],
            [true, true],
        ];
    }
}
