<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;

class SimpleFindDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'inputId')]
    public SimpleEntity|null $entity = null;
}
