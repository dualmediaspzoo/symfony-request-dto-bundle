<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedField;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DeepDottedPathDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DottedPathDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class DottedPathDtoTest extends KernelTestCase
{
    public function testDottedPathReadsNestedRequestValue(): void
    {
        $resolver = static::getService(DtoResolver::class);

        $dto = $resolver->resolve(
            DottedPathDto::class,
            new Request(request: [
                'inner' => ['description' => 'this'],
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame('this', $dto->description);
    }

    public function testOpenApiExposesNestedPath(): void
    {
        $collector = static::getService(FieldCollector::class);

        $described = $collector->collect(DottedPathDto::class, BagEnum::Request);

        static::assertNotNull($described);
        static::assertCount(1, $described->fields);

        $field = $described->fields[0];
        static::assertInstanceOf(DescribedField::class, $field);
        static::assertSame('description', $field->name);
        static::assertSame('inner.description', $field->path);
        static::assertSame('string', $field->oaType);
        static::assertSame(BagEnum::Request, $field->bag);
    }

    public function testOpenApiRequestBodyNestsDottedPath(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(DottedPathDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $schema = $body->content[0]->schema;
        $properties = $schema->properties;

        static::assertCount(1, $properties);
        static::assertSame('inner', $properties[0]->property);
        static::assertSame('object', $properties[0]->type);

        $innerProperties = $properties[0]->properties;
        static::assertCount(1, $innerProperties);
        static::assertSame('description', $innerProperties[0]->property);
        static::assertSame('string', $innerProperties[0]->type);
    }

    public function testDeepDottedPathReadsAndNests(): void
    {
        $resolver = static::getService(DtoResolver::class);
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $dto = $resolver->resolve(
            DeepDottedPathDto::class,
            new Request(request: [
                'inner' => ['child' => ['something' => ['here' => 'field data']]],
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame('field data', $dto->here);

        $described = $collector->collect(DeepDottedPathDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $body = $builder->buildRequestBody($described);
        static::assertNotNull($body);

        $schema = $body->content[0]->schema;

        // inner -> child -> something -> here
        static::assertCount(1, $schema->properties);
        $inner = $schema->properties[0];
        static::assertSame('inner', $inner->property);
        static::assertSame('object', $inner->type);

        static::assertCount(1, $inner->properties);
        $child = $inner->properties[0];
        static::assertSame('child', $child->property);
        static::assertSame('object', $child->type);

        static::assertCount(1, $child->properties);
        $something = $child->properties[0];
        static::assertSame('something', $something->property);
        static::assertSame('object', $something->type);

        static::assertCount(1, $something->properties);
        $here = $something->properties[0];
        static::assertSame('here', $here->property);
        static::assertSame('string', $here->type);
    }
}
