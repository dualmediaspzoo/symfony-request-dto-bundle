<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class EnumDto extends AbstractDto
{
    public ?IntegerEnum $int = null;
    public ?StringEnum $string = null;
}
