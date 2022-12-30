<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\FromKey;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class EnumByKeysDto extends AbstractDto
{
    #[FromKey]
    public ?IntegerEnum $int = null;

    #[FromKey]
    public ?StringEnum $string = null;
}
