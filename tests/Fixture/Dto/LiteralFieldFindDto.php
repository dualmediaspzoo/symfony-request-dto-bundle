<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;

class LiteralFieldFindDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('name', new Literal('literal-value'))]
    public SimpleEntity|null $entity = null;
}
