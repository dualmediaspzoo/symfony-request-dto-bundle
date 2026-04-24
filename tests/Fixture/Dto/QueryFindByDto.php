<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\Product;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\ProductProvider;

#[Bag(BagEnum::Query)]
class QueryFindByDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('code', 'product_code')]
    #[WithObjectProvider(static function (ProductProvider $provider, array $criteria, array $meta): Product|null {
        return $provider->findByCode($criteria);
    })]
    public Product|null $product = null;
}
