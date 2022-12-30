<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\Bag;
use DM\DtoRequestBundle\Enum\BagEnum;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

#[Bag(BagEnum::Query)]
class EnumQueryDto extends AbstractDto
{
    public ?IntegerEnum $int = null;
}
