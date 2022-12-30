<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public ?int $value = null;

    #[Path('floaty_boy')]
    public ?float $floatVal = null;
}
