<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Attributes\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class MainPathFixDto extends AbstractDto
{
    public ?PathFixDto $fix = null;

    #[Path('other_fix_path')]
    public ?PathFixDto $pathFix = null;

    /**
     * @var PathFixDto[]
     */
    public array $nonFixArray = [];

    /**
     * @var PathFixDto[]
     */
    #[Path("some_fix_path_array")]
    public array $fixArray = [];
}
