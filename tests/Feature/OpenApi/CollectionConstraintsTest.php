<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\CollectionConstraintsDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class CollectionConstraintsTest extends KernelTestCase
{
    public function testCountAppliesToContainerAndAllUnwrapsToItems(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(CollectionConstraintsDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $params = $builder->buildParameters($described, '/whatever');
        $byName = [];

        foreach ($params as $p) {
            $byName[(string)$p->name] = $p;
        }

        // Plain DTO array property
        static::assertArrayHasKey('directIds[]', $byName);
        $direct = $byName['directIds[]']->schema;
        static::assertInstanceOf(Schema::class, $direct);
        static::assertSame('array', $direct->type);
        static::assertSame(1, $direct->minItems, 'Assert\\Count(min) must reach the array container');
        static::assertSame(500, $direct->maxItems, 'Assert\\Count(max) must reach the array container');
        static::assertInstanceOf(Items::class, $direct->items);
        static::assertSame('integer', $direct->items->type);
        static::assertSame(0, $direct->items->minimum, 'Assert\\All(Positive) must reach item schema');

        // Virtual Field array
        static::assertArrayHasKey('item_id[]', $byName);
        $virtual = $byName['item_id[]']->schema;
        static::assertInstanceOf(Schema::class, $virtual);
        static::assertSame('array', $virtual->type);
        static::assertSame(1, $virtual->minItems);
        static::assertSame(500, $virtual->maxItems);
        static::assertInstanceOf(Items::class, $virtual->items);
        static::assertSame('integer', $virtual->items->type);
        static::assertSame(0, $virtual->items->minimum);
    }

    public function testCountWithMinAtLeastOneMakesFieldRequired(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(CollectionConstraintsDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $params = $builder->buildParameters($described, '/whatever');
        $byName = [];

        foreach ($params as $p) {
            $byName[(string)$p->name] = $p;
        }

        // Both array fields carry Count(min: 1, max: 500); both must be required.
        static::assertArrayHasKey('directIds[]', $byName);
        static::assertTrue($byName['directIds[]']->required);

        static::assertArrayHasKey('item_id[]', $byName);
        static::assertTrue($byName['item_id[]']->required);
    }
}
