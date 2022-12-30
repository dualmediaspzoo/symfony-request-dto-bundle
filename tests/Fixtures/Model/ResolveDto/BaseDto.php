<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DM\DtoRequestBundle\Attributes\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;

class BaseDto extends AbstractDto
{
    public ?string $field = "";

    public ?SubDto $subBase = null;

    /**
     * @var SubDto[]
     */
    #[Path('array')]
    public array $subDtos = [];

    /**
     * @var SubDto[]
     */
    #[Path('other')]
    public array $secondDtos = [];
}
