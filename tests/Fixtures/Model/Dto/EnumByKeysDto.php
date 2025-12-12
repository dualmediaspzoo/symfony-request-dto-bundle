<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class EnumByKeysDto extends AbstractDto
{
    #[FromKey]
    public IntegerEnum|null $int = null;

    #[FromKey]
    public StringEnum|null $string = null;
}
