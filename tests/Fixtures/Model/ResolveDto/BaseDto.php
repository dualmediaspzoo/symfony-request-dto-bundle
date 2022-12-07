<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DM\DtoRequestBundle\Annotations\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class BaseDto extends AbstractDto
{
    public ?string $field = "";

    public ?SubDto $subBase = null;

    /**
     * @Path("array")
     * @var SubDto[]
     */
    public array $subDtos = [];

    /**
     * @Path("other")
     * @var SubDto[]
     */
    public array $secondDtos = [];
}
