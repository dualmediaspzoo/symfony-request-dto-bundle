<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

class DottedPathDto extends AbstractDto
{
    #[Path('inner.description')]
    public string|null $description = null;
}
