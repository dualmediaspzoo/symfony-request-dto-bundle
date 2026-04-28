<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use Symfony\Component\Validator\Constraints as Assert;

class NestedFindOneByLiteralLeafDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'someId')]
    #[Field('other', new Literal(true))]
    #[WithObjectProvider(static function (TestObjectProvider $provider, array $criteria, array $meta): \stdClass|null {
        return $provider->find($criteria, $meta);
    })]
    #[Assert\NotNull(message: 'entity is required')]
    public \stdClass|null $entity = null;
}
