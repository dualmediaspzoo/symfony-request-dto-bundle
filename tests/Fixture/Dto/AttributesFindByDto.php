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
use Symfony\Component\Validator\Constraints as Assert;

class AttributesFindByDto extends AbstractDto
{
    #[Bag(BagEnum::Attributes)]
    #[FindOneBy]
    #[Field('code', 'combination_id', constraints: new Assert\NotBlank(), description: 'Product identifier')]
    #[WithObjectProvider(static function (ProductProvider $p, array $criteria): Product|null {
        return $p->findByCode($criteria);
    })]
    #[Assert\NotNull(message: 'Product not found')]
    public Product|null $combination = null;
}
