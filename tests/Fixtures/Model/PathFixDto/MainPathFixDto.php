<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Annotations\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class MainPathFixDto extends AbstractDto
{
    public ?PathFixDto $fix = null;

    /**
     * @Path("other_fix_path")
     */
    public ?PathFixDto $pathFix = null;

    /**
     * @var PathFixDto[]
     */
    public array $nonFixArray = [];

    /**
     * @Path("some_fix_path_array")
     * @var PathFixDto[]
     */
    public array $fixArray = [];
}
