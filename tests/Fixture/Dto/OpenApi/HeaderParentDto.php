<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;

class HeaderParentDto extends AbstractDto
{
    #[Bag(BagEnum::Headers)]
    public HeaderChildDto|null $headers = null;
}
