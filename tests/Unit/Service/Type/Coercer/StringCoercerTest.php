<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class StringCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = StringCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'string';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['null', null],
            ['something', 'something'],
        ];
    }
}
