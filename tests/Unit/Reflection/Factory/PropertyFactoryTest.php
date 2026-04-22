<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection\Factory;

use DualMedia\DtoRequestBundle\Coercer\SupportValidator;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints\NotBlank;

#[CoversClass(PropertyFactory::class)]
#[Group('unit')]
#[Group('reflection')]
class PropertyFactoryTest extends TestCase
{
    use ServiceMockHelperTrait;

    private PropertyFactory $factory;

    protected function setUp(): void
    {
        $this->factory = $this->createRealMockedServiceInstance(PropertyFactory::class);
    }

    public function testCreateMinimal(): void
    {
        $type = new BuiltinType(TypeIdentifier::STRING);

        $this->getMockedService(SupportValidator::class)
            ->method('supports')
            ->with($type)
            ->willReturn('string');

        $property = $this->factory->create('name', $type);

        static::assertInstanceOf(Property::class, $property);
        static::assertSame('name', $property->name);
        static::assertSame($type, $property->type);
        static::assertNull($property->bag);
        static::assertNull($property->path);
        static::assertSame('string', $property->coercer);
        static::assertSame([], $property->constraints);
        static::assertSame([], $property->virtual);
        static::assertSame([], $property->meta);
    }

    public function testCreateWithAllParameters(): void
    {
        $type = new BuiltinType(TypeIdentifier::INT);
        $constraint = new NotBlank();
        $meta = [new Format('Y-m-d')];

        $this->getMockedService(SupportValidator::class)
            ->method('supports')
            ->willReturn('integer');

        $property = $this->factory->create(
            'age',
            $type,
            BagEnum::Query,
            'user_age',
            [$constraint],
            [],
            $meta
        );

        static::assertSame('age', $property->name);
        static::assertSame(BagEnum::Query, $property->bag);
        static::assertSame('user_age', $property->path);
        static::assertSame('integer', $property->coercer);
        static::assertSame([$constraint], $property->constraints);
        static::assertSame($meta, $property->meta);
    }

    public function testCreateWithUnsupportedType(): void
    {
        $type = new BuiltinType(TypeIdentifier::MIXED);

        $this->getMockedService(SupportValidator::class)
            ->method('supports')
            ->willReturn(null);

        $property = $this->factory->create('data', $type);
        static::assertNull($property->coercer);
    }
}
