<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use DualMedia\DtoRequestBundle\Type\TypeUtils;
use Symfony\Component\Validator\Constraints as Assert;

#[Bag(BagEnum::Query)]
class CollectionConstraintsDto extends AbstractDto
{
    /** @var list<int> */
    #[Assert\Count(min: 1, max: 500)]
    #[Assert\All(new Assert\Positive())]
    public array $directIds = [];

    /** @var list<\stdClass> */
    #[FindBy]
    #[Field(
        target: 'id',
        input: 'item_id',
        type: TypeUtils::LIST_INT,
        constraints: [
            new Assert\Count(min: 1, max: 500),
            new Assert\All(new Assert\Positive()),
        ],
        description: 'Item ids',
    )]
    #[WithObjectProvider(static function (TestObjectProvider $provider, array $criteria, array $meta): array {
        return [];
    })]
    public array $items = [];
}
