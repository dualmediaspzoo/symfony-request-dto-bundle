<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Annotations\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class RequestEdgeCaseDto extends AbstractDto
{
    /**
     * @Path("")
     * @var PathFixDto[]
     */
    public array $dtos = [];
}
