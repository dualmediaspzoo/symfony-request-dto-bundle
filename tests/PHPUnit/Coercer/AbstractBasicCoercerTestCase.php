<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer;

abstract class AbstractBasicCoercerTestCase extends AbstractCoercerTestCase
{
    protected const EXPECTED_BASIC_TYPE = null;

    public function supportsProvider(): iterable
    {
        if (null === static::EXPECTED_BASIC_TYPE) {
            $this->markTestSkipped('No specified basic type');
        }

        if (!in_array(static::EXPECTED_BASIC_TYPE, self::BASIC_TYPES)) {
            $this->fail(sprintf('Invalid basic type %s', static::EXPECTED_BASIC_TYPE));
        }

        // copy allowed types and remove the one we know here
        $copy = static::BASIC_TYPES;
        unset($copy[array_search(static::EXPECTED_BASIC_TYPE, $copy)]);

        $items = [
            [$this->buildProperty(static::EXPECTED_BASIC_TYPE), true],
            [$this->buildProperty(static::EXPECTED_BASIC_TYPE, true), true],
        ];

        foreach ($copy as $type) {
            $items[] = [
                $this->buildProperty($type),
                false,
            ];
            $items[] = [
                $this->buildProperty($type, true),
                false,
            ];
        }

        yield from $items;
    }

    public function coerceProvider(): iterable
    {
        $inFinal = [];
        $outFinal = [];

        foreach ($this->getCoerceExpected() as $item) {
            [$in, $out] = $item;
            $inFinal[] = $in;
            $outFinal[] = $out;

            yield [
                $this->buildProperty(static::EXPECTED_BASIC_TYPE),
                $in,
                $out,
            ];
            yield [
                $this->buildProperty(static::EXPECTED_BASIC_TYPE, true),
                [$in],
                [$out],
            ];
        }

        yield [
            $this->buildProperty(static::EXPECTED_BASIC_TYPE, true),
            $inFinal,
            $outFinal,
        ];
    }

    /**
     * @return iterable<array<mixed, mixed>>
     */
    abstract protected function getCoerceExpected(): iterable;
}
