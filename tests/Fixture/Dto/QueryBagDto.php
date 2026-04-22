<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;

class QueryBagDto extends AbstractDto
{
    public int|null $page = null;

    public string|null $search = null;

    #[Bag(BagEnum::Request)]
    public string|null $bodyField = null;
}
