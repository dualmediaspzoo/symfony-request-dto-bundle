<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public ?int $subDtoInt = null;

    public ?float $subDtoFloat = null;

    public bool $subDtoBool = false;
}
