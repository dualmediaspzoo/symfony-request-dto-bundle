<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\OpenApi\FieldCollector;
use DualMedia\DtoRequestBundle\OpenApi\SchemaBuilder;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\AttributesFindByDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\Product;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\ProductProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('openapi')]
class AttributesFindByDtoTest extends KernelTestCase
{
    public function testAttributesBagOnFindByPropertyEmitsOnlyPathParameter(): void
    {
        $collector = static::getService(FieldCollector::class);
        $builder = static::getService(SchemaBuilder::class);

        $described = $collector->collect(AttributesFindByDto::class, BagEnum::Request);
        static::assertNotNull($described);

        $parameters = $builder->buildParameters($described, '/foo/{combination_id}');
        $byNameAndIn = [];

        foreach ($parameters as $p) {
            $byNameAndIn[(string)$p->name.'@'.$p->in] = $p;
        }

        static::assertArrayHasKey('combination_id@path', $byNameAndIn, 'must surface combination_id as path parameter');
        static::assertSame('Product identifier', $byNameAndIn['combination_id@path']->description);

        $body = $builder->buildRequestBody($described);
        static::assertNull($body, 'no field should land in the request body when bag is Attributes');
    }

    public function testResolvesProductFromAttributesBag(): void
    {
        $resolver = static::getService(DtoResolver::class);
        $provider = static::getService(ProductProvider::class);
        $product = new Product('SKU-7', 'Combo');
        $provider->store = ['SKU-7' => $product];

        $request = new Request();
        $request->attributes->set('combination_id', 'SKU-7');

        $dto = $resolver->resolve(AttributesFindByDto::class, $request);

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame($product, $dto->combination);
    }

    public function testFailsValidationWhenProductMissing(): void
    {
        $resolver = static::getService(DtoResolver::class);
        $provider = static::getService(ProductProvider::class);
        $provider->store = [];

        $request = new Request();
        $request->attributes->set('combination_id', 'NOT-FOUND');

        $dto = $resolver->resolve(AttributesFindByDto::class, $request);

        static::assertFalse($dto->isValid());
        static::assertNull($dto->combination);

        $messages = [];

        foreach ($dto->getConstraintViolationList() as $violation) {
            $messages[] = $violation->getMessage();
        }

        static::assertContains('Product not found', $messages);
    }
}
