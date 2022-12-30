<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DM\DtoRequestBundle\Attributes\Dto\FromKey;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

class LimitedEnumByKeyDto extends AbstractDto
{
    #[FromKey]
    #[AllowEnum([IntegerEnum::INTEGER_KEY])]
    public ?IntegerEnum $int = null;
}
