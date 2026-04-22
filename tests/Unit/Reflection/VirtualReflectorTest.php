<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Reflection\VirtualReflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;

#[CoversClass(VirtualReflector::class)]
#[Group('unit')]
#[Group('reflection')]
class VirtualReflectorTest extends TestCase
{
    use ServiceMockHelperTrait;

    private VirtualReflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = $this->createRealMockedServiceInstance(VirtualReflector::class);
    }

    public function testLiteralInputReturnsLiteral(): void
    {
        $literal = new Literal(42);
        $fields = $this->reflector->reflect([
            new Field('target_field', $literal),
        ]);

        static::assertArrayHasKey('target_field', $fields);
        static::assertSame($literal, $fields['target_field']);
    }

    public function testDynamicInputReturnsDynamic(): void
    {
        $dynamic = new Dynamic('param_name');
        $fields = $this->reflector->reflect([
            new Field('target_field', $dynamic),
        ]);

        static::assertArrayHasKey('target_field', $fields);
        static::assertSame($dynamic, $fields['target_field']);
    }

    public function testStringInputDelegatesToPropertyFactory(): void
    {
        $type = new BuiltinType(TypeIdentifier::STRING);
        $property = new Property('target_field', $type);

        $this->getMockedService(PropertyFactory::class)
            ->expects(static::once())
            ->method('create')
            ->with('target_field', $type, null, 'input_path', [])
            ->willReturn($property);

        $fields = $this->reflector->reflect([
            new Field('target_field', 'input_path', $type),
        ]);

        static::assertArrayHasKey('target_field', $fields);
        static::assertSame($property, $fields['target_field']);
    }

    public function testCallableTypeIsResolved(): void
    {
        $type = new BuiltinType(TypeIdentifier::INT);
        $property = new Property('field', $type);

        $this->getMockedService(PropertyFactory::class)
            ->method('create')
            ->willReturn($property);

        $fields = $this->reflector->reflect([
            new Field('field', 'path', static fn () => $type),
        ]);

        static::assertArrayHasKey('field', $fields);
    }

    public function testNonFieldAttributesAreSkipped(): void
    {
        $fields = $this->reflector->reflect([
            new \stdClass(),
            'not a field',
            42,
        ]);

        static::assertSame([], $fields);
    }

    public function testEmptyInput(): void
    {
        static::assertSame([], $this->reflector->reflect([]));
    }

    public function testMultipleFieldsMixed(): void
    {
        $literal = new Literal('fixed');
        $dynamic = new Dynamic('provider');
        $type = new BuiltinType(TypeIdentifier::STRING);
        $property = new Property('regular', $type);

        $this->getMockedService(PropertyFactory::class)
            ->method('create')
            ->willReturn($property);

        $fields = $this->reflector->reflect([
            new Field('lit', $literal),
            new Field('dyn', $dynamic),
            new Field('regular', 'path', $type),
        ]);

        static::assertCount(3, $fields);
        static::assertInstanceOf(Literal::class, $fields['lit']);
        static::assertInstanceOf(Dynamic::class, $fields['dyn']);
        static::assertInstanceOf(Property::class, $fields['regular']);
    }
}
