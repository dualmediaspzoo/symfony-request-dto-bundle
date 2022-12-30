<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

class LimitedEnumByKeyDto extends AbstractDto
{
    #[FromKey]
    #[AllowEnum([IntegerEnum::INTEGER_KEY])]
    public ?IntegerEnum $int = null;
}
