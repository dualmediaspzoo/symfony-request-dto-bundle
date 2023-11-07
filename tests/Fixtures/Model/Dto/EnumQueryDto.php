<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Bag;
use DualMedia\DtoRequestBundle\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

#[Bag(BagEnum::Query)]
class EnumQueryDto extends AbstractDto
{
    public IntegerEnum|null $int = null;
}
