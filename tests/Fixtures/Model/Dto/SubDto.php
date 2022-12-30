<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public ?int $subDtoInt = null;

    public ?float $subDtoFloat = null;

    public bool $subDtoBool = false;
}
