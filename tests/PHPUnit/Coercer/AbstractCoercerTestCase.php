<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer;

use DualMedia\DtoRequestBundle\Model\Type\Property;

abstract class AbstractCoercerTestCase extends AbstractMinimalCoercerTestCase
{
    /**
     * @dataProvider coerceProvider
     */
    public function testCoerce(
        Property $property,
        $input,
        $expected
    ): void {
        $this->assertEquals(
            $expected,
            $this->service->coerce('something', $property, $input)->getValue(),
        );
    }

    /**
     * @return iterable<array{0: Property, 1: mixed, 2: mixed}>
     */
    abstract public function coerceProvider(): iterable;
}
