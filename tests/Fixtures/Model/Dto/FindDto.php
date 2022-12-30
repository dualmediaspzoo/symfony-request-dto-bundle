<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DM\DtoRequestBundle\Attributes\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class FindDto extends AbstractDto
{
    #[FindOneBy(
        fields: ['id' => 'id', 'date' => 'whatever'],
        types: ['id' => new Type('int'), 'date' => new Type('datetime')]
    )]
    public ?DummyModel $model = null;
}
