<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer;

use DualMedia\DtoRequestBundle\Model\Type\Property;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class AbstractCoercerTestCase extends AbstractMinimalCoercerTestCase
{
    #[DataProvider('provideCoerceCases')]
    public function testCoerce(
        Property $property,
        mixed $input,
        mixed $expected
    ): void {
        static::assertEquals(
            $expected,
            $this->service->coerce('something', $property, $input)->getValue(),
        );
    }

    /**
     * @return iterable<array{0: Property, 1: mixed, 2: mixed}>
     */
    abstract public static function provideCoerceCases(): iterable;
}
