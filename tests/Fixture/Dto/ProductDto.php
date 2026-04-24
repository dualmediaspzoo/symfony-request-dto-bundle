<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\Product;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\ProductProvider;

class ProductDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('code', 'productCode')]
    #[WithObjectProvider(static function (ProductProvider $provider, array $criteria, array $meta): Product|null {
        return $provider->findByCode($criteria);
    })]
    public Product|null $product = null;
}
