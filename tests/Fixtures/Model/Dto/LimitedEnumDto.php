<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;

class LimitedEnumDto extends AbstractDto
{
    #[AllowEnum([IntegerEnum::IntegerKey])]
    public IntegerEnum|null $int = null;
}
