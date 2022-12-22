<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DM\DtoRequestBundle\Attributes\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public ?int $value = null;

    #[Path('floaty_boy')]
    public ?float $floatVal = null;
}
