<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithAllowedEnum;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\MultiWordEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use DualMedia\DtoRequestBundle\Type\TypeUtils;
use Symfony\Component\TypeInfo\Type;

class VirtualFieldMetaDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'item_id')]
    #[Field(
        target: 'kind',
        input: 'kind',
        type: new Type\EnumType(StringBackedEnum::class),
        description: 'Item kind (matched by case name)',
        meta: [
            new FromKey(),
            new WithAllowedEnum([StringBackedEnum::Foo, StringBackedEnum::Bar]),
        ],
    )]
    #[Field(
        target: 'when',
        input: 'when',
        type: TypeUtils::DATETIME,
        description: 'Date in d/m/Y',
        meta: [new Format('d/m/Y')],
    )]
    #[Field(
        target: 'mood',
        input: 'mood',
        type: new Type\EnumType(MultiWordEnum::class),
        description: 'Restricted mood; only FirstCase is accepted',
        meta: [
            new FromKey(),
            new WithAllowedEnum([MultiWordEnum::FirstCase]),
        ],
    )]
    #[WithObjectProvider(static function (TestObjectProvider $provider, array $criteria, array $meta): \stdClass|null {
        return $provider->find($criteria, $meta);
    })]
    public \stdClass|null $thing = null;
}
