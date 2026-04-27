<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\GroupFilteredConstraintsDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class GroupFilteredConstraintsTest extends KernelTestCase
{
    public function testNonDefaultGroupConstraintsAreIgnoredInOpenApi(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(GroupFilteredConstraintsDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $content = $body->content;
        static::assertIsArray($content);
        $media = $content[0];
        static::assertInstanceOf(MediaType::class, $media);
        $schema = $media->schema;
        static::assertInstanceOf(Schema::class, $schema);

        $byName = [];

        foreach ($schema->properties as $p) {
            $byName[(string)$p->property] = $p;
        }

        // Default group constraints applied
        static::assertArrayHasKey('defaultField', $byName);
        static::assertSame(3, $byName['defaultField']->minLength);
        static::assertContains('defaultField', $schema->required);

        // Non-default group constraints ignored: no minLength, not required
        static::assertArrayHasKey('adminOnlyField', $byName);
        static::assertNotSame(5, $byName['adminOnlyField']->minLength);
        static::assertNotContains('adminOnlyField', $schema->required);

        // Mixed groups (Default + admin) are kept because they include Default
        static::assertArrayHasKey('sharedField', $byName);
        static::assertSame(7, $byName['sharedField']->minLength);
        static::assertContains('sharedField', $schema->required);
    }
}
