<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public int|null $subDtoInt = null;

    public float|null $subDtoFloat = null;

    public bool $subDtoBool = false;
}
