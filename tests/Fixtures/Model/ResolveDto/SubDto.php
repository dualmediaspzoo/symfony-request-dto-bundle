<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class SubDto extends AbstractDto
{
    public int|null $value = null;

    #[Path('floaty_boy')]
    public float|null $floatVal = null;
}
