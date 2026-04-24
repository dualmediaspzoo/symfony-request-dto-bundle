<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\QueryFindByDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('openapi')]
class QueryFindByDtoTest extends KernelTestCase
{
    public function testFindByFieldInheritsClassLevelQueryBag(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(QueryFindByDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $parameters = $builder->buildParameters($described, '/whatever');
        $paramNames = array_map(static fn ($p): string => (string)$p->name, $parameters);
        static::assertContains('product_code', $paramNames, 'virtual field should emit a query parameter');

        $body = $builder->buildRequestBody($described);
        static::assertNull($body, 'no field should end up in the request body when class-level bag is Query');
    }
}
