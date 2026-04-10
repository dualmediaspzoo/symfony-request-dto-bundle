<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\DateTimeCoercer;
use DualMedia\DtoRequestBundle\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(DateTimeCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class DateTimeCoercerTest extends TestCase
{
    private DateTimeCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new DateTimeCoercer(new StringCoercer());
    }

    public function testCoercesIsoString(): void
    {
        $result = $this->coercer->coerce($this->property());
        $coerced = ($result->coerce)('2024-01-15T10:30:00+00:00');
        static::assertInstanceOf(\DateTimeImmutable::class, $coerced);
        static::assertSame('2024-01-15', $coerced->format('Y-m-d'));
    }

    public function testCoercesWithFormat(): void
    {
        $result = $this->coercer->coerce($this->property([new Format('d/m/Y')]));
        $coerced = ($result->coerce)('15/01/2024');
        static::assertInstanceOf(\DateTimeImmutable::class, $coerced);
        static::assertSame('2024-01-15', $coerced->format('Y-m-d'));
    }

    public function testInvalidFormatReturnsOriginal(): void
    {
        $result = $this->coercer->coerce($this->property([new Format('Y-m-d')]));
        $coerced = ($result->coerce)('not-a-date');
        static::assertSame('not-a-date', $coerced);
    }

    public function testInvalidStringWithoutFormatReturnsOriginal(): void
    {
        $result = $this->coercer->coerce($this->property());
        $coerced = ($result->coerce)('totally invalid');
        static::assertSame('totally invalid', $coerced);
    }

    public function testNonStringPassesThrough(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertSame(42, ($result->coerce)(42));
        static::assertNull(($result->coerce)(null));
    }

    public function testConstraints(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    public function testHasInnerStringCoercerResult(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertNotNull($result->inner);
    }

    /**
     * @param list<object> $meta
     */
    private function property(
        array $meta = []
    ): Property {
        return new Property('test', Type::object(\DateTimeImmutable::class), meta: $meta);
    }
}
