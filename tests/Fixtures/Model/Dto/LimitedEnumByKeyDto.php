<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Annotations\Dto\AllowEnum;
use DM\DtoRequestBundle\Annotations\Dto\FromKey;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

class LimitedEnumByKeyDto extends AbstractDto
{
    /**
     * @FromKey()
     * @AllowEnum({"INTEGER_KEY"})
     */
    public ?IntegerEnum $int = null;
}
