<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\CoercionUtils;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(CoercionUtils::class)]
#[Group('unit')]
#[Group('coercer')]
class CoercionUtilsTest extends TestCase
{
    public function testScalarCoercion(): void
    {
        $property = new Property('test', Type::int());
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => (int)$val,
            new TypeConstraint(type: 'int')
        );

        static::assertSame(42, ($result->coerce)('42'));
    }

    public function testScalarConstraintsNotWrapped(): void
    {
        $property = new Property('test', Type::int());
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => $val,
            new TypeConstraint(type: 'int')
        );

        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    public function testCollectionCoercionAppliesPerElement(): void
    {
        $property = new Property(
            'test',
            Type::list(Type::int())
        );
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => (int)$val,
            new TypeConstraint(type: 'int')
        );

        static::assertSame([1, 2, 3], ($result->coerce)(['1', '2', '3']));
    }

    public function testCollectionConstraintsWrappedInAll(): void
    {
        $property = new Property(
            'test',
            Type::list(Type::int())
        );
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => $val,
            new TypeConstraint(type: 'int')
        );

        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(All::class, $result->constraints[0]);
    }

    public function testScalarValueNotWrappedInArray(): void
    {
        $property = new Property('test', Type::string());
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => strtoupper((string)$val),
            new TypeConstraint(type: 'string')
        );

        static::assertSame('HELLO', ($result->coerce)('hello'));
    }

    public function testInnerResultIsPassedThrough(): void
    {
        $property = new Property('test', Type::int());
        $inner = new Result(static fn (mixed $val) => $val);

        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => $val,
            new TypeConstraint(type: 'int'),
            $inner
        );

        static::assertSame($inner, $result->inner);
    }

    public function testConstraintArrayFlattened(): void
    {
        $property = new Property('test', Type::int());
        $result = CoercionUtils::coerce(
            $property,
            static fn (mixed $val) => $val,
            [new TypeConstraint(type: 'int'), new TypeConstraint(type: 'numeric')]
        );

        static::assertCount(2, $result->constraints);
    }
}
