<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Attributes\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class RequestEdgeCaseDto extends AbstractDto
{
    /**
     * @var PathFixDto[]
     */
    #[Path('')]
    public array $dtos = [];
}
