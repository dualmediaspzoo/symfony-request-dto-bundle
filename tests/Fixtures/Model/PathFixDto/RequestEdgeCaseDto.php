<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class RequestEdgeCaseDto extends AbstractDto
{
    /**
     * @var PathFixDto[]
     */
    #[Path('')]
    public array $dtos = [];
}
