<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class EnumDto extends AbstractDto
{
    public ?IntegerEnum $int = null;
    public ?StringEnum $string = null;
}
