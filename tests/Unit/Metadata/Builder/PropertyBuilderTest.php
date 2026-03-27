<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Metadata\Builder;

use DualMedia\DtoRequestBundle\Metadata\Builder\PropertyBuilder;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
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
        $property = new PropertyBuilder(
            name: 'title',
            type: 'string',
        )->build();

        static::assertSame('title', $property->name);
        static::assertSame('string', $property->type);
        static::assertSame(BagEnum::Request, $property->bag);
        static::assertNull($property->path);
        static::assertNull($property->subType);
        static::assertNull($property->fqcn);
        static::assertFalse($property->collection);
        static::assertNull($property->coercerKey);
        static::assertSame([], $property->constraints);
        static::assertFalse($property->requiresRuntimeResolve);
        static::assertSame([], $property->children);
        static::assertSame([], $property->meta);
    }

    public function testBuildWithAllFields(): void
    {
        $constraint = new NotBlank();
        $child = new PropertyBuilder(
            name: 'child',
            type: 'int',
        )->build();

        $property = new PropertyBuilder(
            name: 'items',
            type: 'object',
            bag: BagEnum::Query,
        )
            ->path('custom_path')
            ->subType('int')
            ->fqcn(\DateTimeImmutable::class)
            ->collection()
            ->coercerKey('datetime')
            ->requiresRuntimeResolve()
            ->constraint($constraint)
            ->child($child)
            ->meta('some_data')
            ->build();

        static::assertSame('items', $property->name);
        static::assertSame('object', $property->type);
        static::assertSame(BagEnum::Query, $property->bag);
        static::assertSame('custom_path', $property->path);
        static::assertSame('int', $property->subType);
        static::assertSame(\DateTimeImmutable::class, $property->fqcn);
        static::assertTrue($property->collection);
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
            type: 'string',
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
            type: 'string',
        )
            ->constraint($notBlank)
            ->constraints([$notNull])
            ->build();

        static::assertSame([$notBlank, $notNull], $property->constraints);
    }

    public function testChildrenAccumulate(): void
    {
        $childA = new PropertyBuilder(name: 'a', type: 'string')->build();
        $childB = new PropertyBuilder(name: 'b', type: 'int')->build();

        $property = new PropertyBuilder(
            name: 'parent',
            type: 'object',
        )
            ->child($childA)
            ->child($childB)
            ->build();

        static::assertSame(['a' => $childA, 'b' => $childB], $property->children);
    }

    public function testChildrenBatchAccumulate(): void
    {
        $childA = new PropertyBuilder(name: 'a', type: 'string')->build();
        $childB = new PropertyBuilder(name: 'b', type: 'int')->build();

        $property = new PropertyBuilder(
            name: 'parent',
            type: 'object',
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
            type: 'string',
        )->build();

        static::assertInstanceOf(Property::class, $property);

        $reflection = new \ReflectionClass($property);
        static::assertTrue($reflection->isReadOnly());
    }

    public function testRealPathFallsBackToName(): void
    {
        $withoutPath = new PropertyBuilder(
            name: 'field_name',
            type: 'string',
        )->build();

        static::assertSame('field_name', $withoutPath->getRealPath());

        $withPath = new PropertyBuilder(
            name: 'field_name',
            type: 'string',
        )
            ->path('custom')
            ->build();

        static::assertSame('custom', $withPath->getRealPath());
    }
}
