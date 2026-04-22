<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\FloatCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(FloatCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class FloatCoercerTest extends TestCase
{
    private FloatCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new FloatCoercer();
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
        yield 'float string' => ['3.14', 3.14];
        yield 'integer string' => ['42', 42.0];
        yield 'zero string' => ['0', 0.0];
        yield 'negative float' => ['-2.5', -2.5];
        yield 'scientific notation' => ['1e3', 1000.0];
        yield 'string null becomes null' => ['null', null];
        yield 'non-numeric string passes through' => ['abc', 'abc'];
        yield 'actual float passes through' => [3.14, 3.14];
        yield 'null passes through' => [null, null];
    }

    public function testConstraints(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    private function property(): Property
    {
        return new Property('test', Type::float());
    }
}
