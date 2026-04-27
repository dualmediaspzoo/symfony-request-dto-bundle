<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\AsRootWithParameterDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\RootPathListDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\Items;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class AsRootBodyTest extends KernelTestCase
{
    public function testAsRootListDtoBodyIsArrayNotEmptyKeyProperty(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(RootPathListDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);

        // Body schema must BE the array, not an object with an empty-string-named property.
        static::assertSame('array', $schema->type, 'AsRoot list must surface as the body itself');
        static::assertSame(Generator::UNDEFINED, $schema->properties, 'no top-level properties expected');
        static::assertNotSame([''], $schema->required, 'must not advertise an empty-string required key');

        // Items mirror the child DTO's properties.
        $items = $schema->items;
        static::assertInstanceOf(Items::class, $items);
        static::assertSame('object', $items->type);

        $names = array_map(static fn ($p): string => (string)$p->property, $items->properties);
        sort($names);
        static::assertSame(['boolField', 'floatField', 'intField', 'stringField'], $names);
    }

    public function testAsRootBodyCoexistsWithNonBodyParameters(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(AsRootWithParameterDto::class, BagEnum::Request);
        static::assertNotNull($described);

        // Body keeps the AsRoot list as the array schema — the Attributes
        // parameter must NOT contaminate the body shape.
        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);
        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);
        static::assertSame('array', $schema->type);
        static::assertSame(Generator::UNDEFINED, $schema->properties);

        // The Attributes-bagged property surfaces as a path parameter when the
        // route declares it; otherwise it's filtered out by collectParameters.
        $params = $builder->buildParameters($described, '/foo/{weird}');
        $byNameAndIn = [];

        foreach ($params as $p) {
            $byNameAndIn[(string)$p->name.'@'.$p->in] = $p;
        }

        static::assertArrayHasKey('weird@path', $byNameAndIn);
        static::assertSame('integer', $byNameAndIn['weird@path']->schema->type);
    }
}
