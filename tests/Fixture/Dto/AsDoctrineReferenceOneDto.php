<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;

class AsDoctrineReferenceOneDto extends AbstractDto
{
    #[FindOneBy]
    #[AsDoctrineReference]
    #[Field('id', 'inputId')]
    public SimpleEntity|null $entity = null;
}