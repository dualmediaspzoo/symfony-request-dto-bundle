<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;

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
