<?php

namespace DM\DtoRequestBundle\Tests\PHPUnit\Coercer;

use DM\DtoRequestBundle\Model\Type\Property;

abstract class AbstractCoercerTestCase extends AbstractMinimalCoercerTestCase
{
    /**
     * @param Property $property
     * @param mixed $input
     * @param mixed $expected
     *
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
