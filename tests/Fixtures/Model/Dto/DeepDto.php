<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class DeepDto extends AbstractDto
{
    #[Path('something.deep')]
    public ?string $pathed = null;
}
