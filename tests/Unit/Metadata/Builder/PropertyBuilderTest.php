<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Metadata\Builder;

use DualMedia\DtoRequestBundle\Metadata\Builder\PropertyBuilder;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

#[Group('unit')]
#[Group('metadata')]
#[Group('builder')]
#[CoversClass(PropertyBuilder::class)]
class PropertyBuilderTest extends TestCase
{
    public function testBuildWithDefaults(): void
    {
        $type = new Type(type: 'string', collection: false);

        $property = new PropertyBuilder(
            name: 'title',
            type: $type,
        )->build();

        static::assertSame('title', $property->name);
        static::assertSame($type, $property->type);
        static::assertSame(BagEnum::Request, $property->bag);
        static::assertNull($property->path);
        static::assertNull($property->coercerKey);
        static::assertSame([], $property->constraints);
        static::assertFalse($property->requiresRuntimeResolve);
        static::assertSame([], $property->children);
        static::assertSame([], $property->meta);
    }

    public function testBuildWithAllFields(): void
    {
        $constraint = new NotBlank();
        $childType = new Type(type: 'int', collection: false);
        $child = new PropertyBuilder(
            name: 'child',
            type: $childType,
        )->build();

        $type = new Type(
            type: 'object',
            collection: true,
            subType: 'int',
        );

        $property = new PropertyBuilder(
            name: 'items',
            type: $type,
            bag: BagEnum::Query,
        )
            ->path('custom_path')
            ->coercerKey('datetime')
            ->requiresRuntimeResolve()
            ->constraint($constraint)
            ->child($child)
            ->meta('some_data')
            ->build();

        static::assertSame('items', $property->name);
        static::assertSame($type, $property->type);
        static::assertSame(BagEnum::Query, $property->bag);
        static::assertSame('custom_path', $property->path);
        static::assertSame('datetime', $property->coercerKey);
        static::assertTrue($property->requiresRuntimeResolve);
        static::assertSame([$constraint], $property->constraints);
        static::assertSame(['child' => $child], $property->children);
        static::assertSame(['some_data'], $property->meta);
    }

    public function testConstraintsAccumulate(): void
    {
        $notBlank = new NotBlank();
        $notNull = new NotNull();

        $property = new PropertyBuilder(
            name: 'field',
            type: new Type(type: 'string', collection: false),
        )
            ->constraint($notBlank)
            ->constraint($notNull)
            ->build();

        static::assertSame([$notBlank, $notNull], $property->constraints);
    }

    public function testConstraintsBatchAccumulate(): void
    {
        $notBlank = new NotBlank();
        $notNull = new NotNull();

        $property = new PropertyBuilder(
            name: 'field',
            type: new Type(type: 'string', collection: false),
        )
            ->constraint($notBlank)
            ->constraints([$notNull])
            ->build();

        static::assertSame([$notBlank, $notNull], $property->constraints);
    }

    public function testChildrenAccumulate(): void
    {
        $type = new Type(type: 'string', collection: false);
        $childA = new PropertyBuilder(name: 'a', type: $type)->build();
        $childB = new PropertyBuilder(
            name: 'b',
            type: new Type(type: 'int', collection: false),
        )->build();

        $property = new PropertyBuilder(
            name: 'parent',
            type: new Type(type: 'object', collection: false),
        )
            ->child($childA)
            ->child($childB)
            ->build();

        static::assertSame(['a' => $childA, 'b' => $childB], $property->children);
    }

    public function testChildrenBatchAccumulate(): void
    {
        $type = new Type(type: 'string', collection: false);
        $childA = new PropertyBuilder(name: 'a', type: $type)->build();
        $childB = new PropertyBuilder(
            name: 'b',
            type: new Type(type: 'int', collection: false),
        )->build();

        $property = new PropertyBuilder(
            name: 'parent',
            type: new Type(type: 'object', collection: false),
        )
            ->child($childA)
            ->children(['b' => $childB])
            ->build();

        static::assertSame(['a' => $childA, 'b' => $childB], $property->children);
    }

    public function testBuildReturnsReadonlyProperty(): void
    {
        $property = new PropertyBuilder(
            name: 'test',
            type: new Type(type: 'string', collection: false),
        )->build();

        static::assertInstanceOf(Property::class, $property);

        $reflection = new \ReflectionClass($property);
        static::assertTrue($reflection->isReadOnly());
    }

    public function testRealPathFallsBackToName(): void
    {
        $type = new Type(type: 'string', collection: false);

        $withoutPath = new PropertyBuilder(
            name: 'field_name',
            type: $type,
        )->build();

        static::assertSame('field_name', $withoutPath->getRealPath());

        $withPath = new PropertyBuilder(
            name: 'field_name',
            type: $type,
        )
            ->path('custom')
            ->build();

        static::assertSame('custom', $withPath->getRealPath());
    }
}