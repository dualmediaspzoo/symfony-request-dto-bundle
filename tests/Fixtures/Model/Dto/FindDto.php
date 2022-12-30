<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attributes\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class FindDto extends AbstractDto
{
    #[FindOneBy(
        fields: ['id' => 'id', 'date' => 'whatever'],
        types: ['id' => new Type('int'), 'date' => new Type('datetime')]
    )]
    public ?DummyModel $model = null;
}
