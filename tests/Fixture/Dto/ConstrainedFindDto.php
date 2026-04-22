<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints as Assert;

class ConstrainedFindDto extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'inputId', new BuiltinType(TypeIdentifier::INT), [new Assert\NotNull(), new Assert\Positive()])]
    public SimpleEntity|null $entity = null;
}
