<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\Validator\Constraints as Assert;

class FindWithSecondErrorDto extends AbstractDto
{
    #[FindOneBy(
        fields: ['id' => 'something_id'],
        constraints: ['id' => new Assert\NotBlank()],
        types: ['id' => new Type('int')]
    )]
    public DummyModel|null $model = null;
}
