<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\BooleanCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(BooleanCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class BooleanCoercerTest extends TestCase
{
    private BooleanCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new BooleanCoercer();
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
        yield 'string 1 becomes true' => ['1', true];
        yield 'string 0 becomes false' => ['0', false];
        yield 'string true becomes true' => ['true', true];
        yield 'string false becomes false' => ['false', false];
        yield 'string null becomes null' => ['null', null];
        yield 'actual bool true passes through' => [true, true];
        yield 'actual bool false passes through' => [false, false];
        yield 'random string passes through' => ['hello', 'hello'];
        yield 'int 1 coerces to true' => [1, true];
        yield 'int 0 coerces to false' => [0, false];
        yield 'null passes through' => [null, null];
    }

    public function testConstraints(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    public function testNoInnerResult(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertNull($result->inner);
    }

    private function property(): Property
    {
        return new Property('test', Type::bool());
    }
}
