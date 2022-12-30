<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

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
