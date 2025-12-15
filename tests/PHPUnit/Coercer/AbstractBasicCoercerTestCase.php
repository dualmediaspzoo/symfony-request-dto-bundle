<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer;

abstract class AbstractBasicCoercerTestCase extends AbstractCoercerTestCase
{
    protected const EXPECTED_BASIC_TYPE = null;

    public static function provideSupportsCases(): iterable
    {
        if (null === static::EXPECTED_BASIC_TYPE) {
            static::markTestSkipped('No specified basic type');
        }

        if (!in_array(static::EXPECTED_BASIC_TYPE, self::BASIC_TYPES, true)) {
            static::fail(sprintf('Invalid basic type %s', static::EXPECTED_BASIC_TYPE));
        }

        // copy allowed types and remove the one we know here
        $copy = static::BASIC_TYPES;
        unset($copy[array_search(static::EXPECTED_BASIC_TYPE, $copy, true)]);

        $items = [
            [static::buildProperty(static::EXPECTED_BASIC_TYPE), true],
            [static::buildProperty(static::EXPECTED_BASIC_TYPE, true), true],
        ];

        foreach ($copy as $type) {
            $items[] = [
                static::buildProperty($type),
                false,
            ];
            $items[] = [
                static::buildProperty($type, true),
                false,
            ];
        }

        yield from $items;
    }

    public static function provideCoerceCases(): iterable
    {
        $inFinal = [];
        $outFinal = [];

        foreach (static::getCoerceExpected() as $item) {
            [$in, $out] = $item;
            $inFinal[] = $in;
            $outFinal[] = $out;

            yield [
                static::buildProperty(static::EXPECTED_BASIC_TYPE),
                $in,
                $out,
            ];
            yield [
                static::buildProperty(static::EXPECTED_BASIC_TYPE, true),
                [$in],
                [$out],
            ];
        }

        yield [
            static::buildProperty(static::EXPECTED_BASIC_TYPE, true),
            $inFinal,
            $outFinal,
        ];
    }

    /**
     * @return iterable<array<mixed, mixed>>
     */
    abstract protected static function getCoerceExpected(): iterable;
}
