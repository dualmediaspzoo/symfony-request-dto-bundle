<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class DeepDto extends AbstractDto
{
    #[Path('something.deep')]
    public string|null $pathed = null;
}
