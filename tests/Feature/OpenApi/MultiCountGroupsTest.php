<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\MultiCountGroupsDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class MultiCountGroupsTest extends KernelTestCase
{
    public function testGroupFilteredMinDoesNotMakeFieldRequired(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(MultiCountGroupsDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);

        // The min-bearing Count is in the 'full' group → must be ignored.
        // The Default-group Count only has max, so the field MUST NOT be required.
        static::assertNotContains('field', $schema->required);

        // The Default-group Count(max: 10) should still surface on the array.
        $byName = [];

        foreach ($schema->properties as $p) {
            $byName[(string)$p->property] = $p;
        }

        static::assertArrayHasKey('field', $byName);
        static::assertSame(10, $byName['field']->maxItems);
    }
}