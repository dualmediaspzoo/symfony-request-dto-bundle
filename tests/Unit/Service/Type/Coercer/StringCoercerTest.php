<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;

class StringCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = StringCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'string';

    protected function getCoerceExpected(): iterable
    {
        return [
            ['something', 'something'],
        ];
    }
}
