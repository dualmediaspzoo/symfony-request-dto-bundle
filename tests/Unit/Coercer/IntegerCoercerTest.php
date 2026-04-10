<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(IntegerCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class IntegerCoercerTest extends TestCase
{
    private IntegerCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new IntegerCoercer();
    }

    #[DataProvider('provideCoercionCases')]
    public function testCoercion(
        mixed $input,
        mixed $expected
    ): void {
        $result = $this->coercer->coerce($this->property());
        static::assertSame($expected, ($result->coerce)($input));
    }

    /**
     * @return iterable<string, array{mixed, mixed}>
     */
    public static function provideCoercionCases(): iterable
    {
        yield 'numeric string to int' => ['42', 42];
        yield 'zero string' => ['0', 0];
        yield 'negative string' => ['-5', -5];
        yield 'float string stays as-is' => ['3.14', '3.14'];
        yield 'string null becomes null' => ['null', null];
        yield 'non-numeric string passes through' => ['abc', 'abc'];
        yield 'actual int passes through' => [42, 42];
        yield 'null passes through' => [null, null];
        yield 'empty string passes through' => ['', ''];
    }

    public function testConstraints(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    private function property(): Property
    {
        return new Property('test', Type::int());
    }
}
