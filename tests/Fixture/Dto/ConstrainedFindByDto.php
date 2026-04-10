<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Type\TypeUtils;
use Symfony\Component\Validator\Constraints as Assert;

class ConstrainedFindByDto extends AbstractDto
{
    /**
     * @var list<SimpleEntity>
     */
    #[FindBy]
    #[Field('id', 'inputIds', TypeUtils::LIST_INT, new Assert\All([new Assert\Positive()]))]
    public array $entities = [];
}
