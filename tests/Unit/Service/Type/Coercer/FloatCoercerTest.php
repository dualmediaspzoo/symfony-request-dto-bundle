<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Service\Type\Coercer\FloatCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class FloatCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = FloatCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'float';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['0', 0.0],
            ['1', 1.0],
            [0, 0.0],
            [1, 1.0],
            ['14.44', 14.44],
        ];
    }
}
