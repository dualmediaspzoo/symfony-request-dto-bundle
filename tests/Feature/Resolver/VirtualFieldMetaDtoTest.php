<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VirtualFieldMetaDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\MultiWordEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use OpenApi\Annotations\MediaType;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class VirtualFieldMetaDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private TestObjectProvider $provider;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->provider = static::getService(TestObjectProvider::class);
        $this->provider->store = [];
        $this->provider->calls = [];
    }

    public function testVirtualFieldsCoerceWithFromKeyAndFormat(): void
    {
        $expected = new \stdClass();
        $this->provider->store['7'] = $expected;

        $dto = $this->resolver->resolve(
            VirtualFieldMetaDto::class,
            new Request(request: [
                'item_id' => '7',
                'kind' => 'Foo',           // matched by case NAME via FromKey
                'when' => '15/01/2025',    // parsed via Format('d/m/Y')
                'mood' => 'FirstCase',     // restricted via WithAllowedEnum
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame($expected, $dto->thing);

        static::assertCount(1, $this->provider->calls);
        $criteria = $this->provider->calls[0]['criteria'];

        static::assertSame(StringBackedEnum::Foo, $criteria['kind']);
        static::assertInstanceOf(\DateTimeImmutable::class, $criteria['when']);
        static::assertSame('2025-01-15', $criteria['when']->format('Y-m-d'));
        static::assertSame(MultiWordEnum::FirstCase, $criteria['mood']);
    }

    public function testFromKeyRejectsBackedValue(): void
    {
        $dto = $this->resolver->resolve(
            VirtualFieldMetaDto::class,
            new Request(request: [
                'item_id' => '7',
                'kind' => 'foo',           // backed value, not the case name — must fail with FromKey
                'when' => '15/01/2025',
                'mood' => 'FirstCase',
            ])
        );

        static::assertFalse($dto->isValid());
    }

    public function testWithAllowedEnumRejectsCaseOutsideAllowedSet(): void
    {
        $dto = $this->resolver->resolve(
            VirtualFieldMetaDto::class,
            new Request(request: [
                'item_id' => '7',
                'kind' => 'Foo',
                'when' => '15/01/2025',
                'mood' => 'SecondCase',    // exists on the enum but excluded by WithAllowedEnum
            ])
        );

        static::assertFalse($dto->isValid());
    }

    public function testFormatRejectsWrongDatePattern(): void
    {
        $dto = $this->resolver->resolve(
            VirtualFieldMetaDto::class,
            new Request(request: [
                'item_id' => '7',
                'kind' => 'Foo',
                'when' => '2025-01-15',    // ISO; Format requires d/m/Y
                'mood' => 'FirstCase',
            ])
        );

        static::assertFalse($dto->isValid());
    }

    public function testOpenApiReflectsFieldMeta(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(VirtualFieldMetaDto::class, BagEnum::Request);
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

        static::assertArrayHasKey('kind', $byName);
        $kind = $byName['kind'];
        static::assertSame('string', $kind->type);
        static::assertSame(['Foo', 'Bar'], $kind->enum, 'FromKey + WithAllowedEnum should expose case names');
        static::assertSame('Item kind (matched by case name)', $kind->description);

        static::assertArrayHasKey('when', $byName);
        $when = $byName['when'];
        static::assertSame('string', $when->type);
        static::assertSame('d/m/Y', $when->format, 'Format(d/m/Y) must surface as schema format');
    }
}
