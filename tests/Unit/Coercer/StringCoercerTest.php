<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(StringCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class StringCoercerTest extends TestCase
{
    private StringCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new StringCoercer();
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
        yield 'regular string passes through' => ['hello', 'hello'];
        yield 'string null becomes null' => ['null', null];
        yield 'empty string passes through' => ['', ''];
        yield 'int passes through' => [42, 42];
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
        return new Property('test', Type::string());
    }
}
