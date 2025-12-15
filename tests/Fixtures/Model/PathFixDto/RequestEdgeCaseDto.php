<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DualMedia\DtoRequestBundle\Attribute\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

class RequestEdgeCaseDto extends AbstractDto
{
    /**
     * @var PathFixDto[]
     */
    #[Path('')]
    public array $dtos = [];
}
