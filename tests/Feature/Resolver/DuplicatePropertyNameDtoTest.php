<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DuplicatePropertyNameDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class DuplicatePropertyNameDtoTest extends KernelTestCase
{
    public function testDuplicateEmittedNamesAreDeduped(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(DuplicatePropertyNameDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);

        $matching = array_values(array_filter(
            $schema->properties,
            static fn ($p): bool => 'item_id' === $p->property
        ));

        static::assertCount(1, $matching, 'duplicate "item_id" must collapse to a single entry');
    }
}
