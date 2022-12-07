<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Annotations\Dto\Bag;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

/**
 * @Bag("query")
 */
class EnumQueryDto extends AbstractDto
{
    public ?IntegerEnum $int = null;
}
