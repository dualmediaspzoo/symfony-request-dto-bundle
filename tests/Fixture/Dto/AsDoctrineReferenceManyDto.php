<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Type\TypeUtils;

class AsDoctrineReferenceManyDto extends AbstractDto
{
    /**
     * @var list<SimpleEntity>
     */
    #[FindBy]
    #[AsDoctrineReference]
    #[Field('id', 'inputIds', TypeUtils::LIST_INT)]
    public array $entities = [];
}
