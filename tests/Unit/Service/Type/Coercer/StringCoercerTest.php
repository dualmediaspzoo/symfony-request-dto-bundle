<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Service\Type\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractBasicCoercerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[Group('coercer')]
#[CoversClass(StringCoercer::class)]
class StringCoercerTest extends AbstractBasicCoercerTestCase
{
    protected const SERVICE_ID = StringCoercer::class;
    protected const EXPECTED_BASIC_TYPE = 'string';

    protected static function getCoerceExpected(): iterable
    {
        return [
            ['null', null],
            ['something', 'something'],
        ];
    }
}
