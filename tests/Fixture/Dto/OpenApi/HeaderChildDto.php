<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

class HeaderChildDto extends AbstractDto
{
    #[Path('X-Main')]
    public string|null $mainHeader = null;

    #[Path('X-Other')]
    public string|null $otherHeader = null;
}
