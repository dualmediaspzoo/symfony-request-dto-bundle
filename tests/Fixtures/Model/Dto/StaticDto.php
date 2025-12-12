<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class StaticDto extends AbstractDto
{
    #[FindOneBy(
        fields: ['id' => 'something_id', 'second' => 'something_second'],
        types: ['id' => new Type('int')],
        static: ['static' => 1551]
    )]
    public DummyModel|null $model = null;
}
