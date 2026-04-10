<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\EnumCoercer;
use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(EnumCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class EnumCoercerTest extends TestCase
{
    private EnumCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new EnumCoercer(new StringCoercer(), new IntegerCoercer());
    }

    public function testStringBackedEnumCoercion(): void
    {
        $property = new Property(
            'test',
            Type::enum(StringBackedEnum::class, Type::string())
        );
        $result = $this->coercer->coerce($property);
        static::assertSame(StringBackedEnum::Foo, ($result->coerce)('foo'));
        static::assertSame(StringBackedEnum::Bar, ($result->coerce)('bar'));
    }

    public function testStringBackedEnumInvalidValue(): void
    {
        $property = new Property(
            'test',
            Type::enum(StringBackedEnum::class, Type::string())
        );
        $result = $this->coercer->coerce($property);
        static::assertSame('invalid', ($result->coerce)('invalid'));
    }

    public function testIntBackedEnumCoercion(): void
    {
        $property = new Property(
            'test',
            Type::enum(IntBackedEnum::class, Type::int())
        );
        $result = $this->coercer->coerce($property);
        static::assertSame(IntBackedEnum::One, ($result->coerce)(1));
        static::assertSame(IntBackedEnum::Two, ($result->coerce)(2));
    }

    public function testIntBackedEnumHasIntegerInner(): void
    {
        $property = new Property(
            'test',
            Type::enum(IntBackedEnum::class, Type::int())
        );
        $result = $this->coercer->coerce($property);
        static::assertNotNull($result->inner);
    }

    public function testStringBackedEnumHasStringInner(): void
    {
        $property = new Property(
            'test',
            Type::enum(StringBackedEnum::class, Type::string())
        );
        $result = $this->coercer->coerce($property);
        static::assertNotNull($result->inner);
    }

    public function testFromKeyCoercion(): void
    {
        $property = new Property(
            'test',
            Type::enum(PureEnum::class),
            meta: [new FromKey()]
        );
        $result = $this->coercer->coerce($property);
        static::assertSame(PureEnum::Alpha, ($result->coerce)('Alpha'));
        static::assertSame(PureEnum::Beta, ($result->coerce)('Beta'));
    }

    public function testFromKeyInvalidName(): void
    {
        $property = new Property(
            'test',
            Type::enum(PureEnum::class),
            meta: [new FromKey()]
        );
        $result = $this->coercer->coerce($property);
        static::assertSame('NonExistent', ($result->coerce)('NonExistent'));
    }

    public function testFromKeyNonStringPassesThrough(): void
    {
        $property = new Property(
            'test',
            Type::enum(PureEnum::class),
            meta: [new FromKey()]
        );
        $result = $this->coercer->coerce($property);
        static::assertSame(42, ($result->coerce)(42));
    }

    public function testPureEnumWithoutFromKeyNoCoercion(): void
    {
        $property = new Property(
            'test',
            Type::enum(PureEnum::class)
        );
        $result = $this->coercer->coerce($property);
        static::assertSame('Alpha', ($result->coerce)('Alpha'));
        static::assertNull($result->inner);
    }

    public function testConstraintsAreTypeConstraint(): void
    {
        $property = new Property(
            'test',
            Type::enum(StringBackedEnum::class, Type::string())
        );
        $result = $this->coercer->coerce($property);
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }
}
