<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\OpenApi;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\EnumDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class EnumChoicesTest extends KernelTestCase
{
    public function testEnumChoicesUseDefaultBackingValuesAndCaseNamesWhenFromKey(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(EnumDto::class, BagEnum::Request);
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

        // int-backed enum → integer schema with backing values
        static::assertArrayHasKey('intEnum', $byName);
        static::assertSame('integer', $byName['intEnum']->type);
        static::assertSame([1, 2], $byName['intEnum']->enum);

        // string-backed enum → string schema with backing values
        static::assertArrayHasKey('stringEnum', $byName);
        static::assertSame('string', $byName['stringEnum']->type);
        static::assertSame(['foo', 'bar'], $byName['stringEnum']->enum);

        // FromKey on int-backed enum → string schema with case names
        static::assertArrayHasKey('intEnumByKey', $byName);
        static::assertSame('string', $byName['intEnumByKey']->type);
        static::assertSame(['One', 'Two'], $byName['intEnumByKey']->enum);

        // FromKey on string-backed enum → string schema with case names
        static::assertArrayHasKey('stringEnumByKey', $byName);
        static::assertSame('string', $byName['stringEnumByKey']->type);
        static::assertSame(['Foo', 'Bar'], $byName['stringEnumByKey']->enum);

        // FromKey on a pure (non-backed) enum → string schema with case names
        static::assertArrayHasKey('pureEnumByKey', $byName);
        static::assertSame('string', $byName['pureEnumByKey']->type);
        static::assertNotEmpty($byName['pureEnumByKey']->enum);
    }
}
