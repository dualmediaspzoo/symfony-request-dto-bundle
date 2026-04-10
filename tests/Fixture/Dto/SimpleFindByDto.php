<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints as Assert;

class SimpleFindByDto extends AbstractDto
{
    /**
     * @var list<SimpleEntity>
     */
    #[FindBy]
    #[Field('id', 'inputIds', static function () { return Type::list(Type::int()); }, new Assert\All([new Assert\NotNull()]))]
    public array $entities = [];
}
