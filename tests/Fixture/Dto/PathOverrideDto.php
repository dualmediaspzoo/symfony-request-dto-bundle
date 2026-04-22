<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

class PathOverrideDto extends AbstractDto
{
    #[Path('custom-int')]
    public int|null $intField = null;
}
