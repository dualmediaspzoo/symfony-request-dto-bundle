<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ProductDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\Product;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\ProductProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ProductDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private ProductProvider $provider;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->provider = static::getService(ProductProvider::class);
        $this->provider->store = [];
    }

    public function testCustomProviderReturnsEntity(): void
    {
        $product = new Product('SKU-1', 'Widget');
        $this->provider->store['SKU-1'] = $product;

        $dto = $this->resolver->resolve(
            ProductDto::class,
            new Request(request: ['productCode' => 'SKU-1'])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertSame($product, $dto->product);
    }

    public function testCustomProviderReturnsNullWhenNotFound(): void
    {
        $dto = $this->resolver->resolve(
            ProductDto::class,
            new Request(request: ['productCode' => 'MISSING'])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->product);
    }
}
