<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class EnumByKeysDto extends AbstractDto
{
    #[FromKey]
    public ?IntegerEnum $int = null;

    #[FromKey]
    public ?StringEnum $string = null;
}
