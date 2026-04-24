<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

class DuplicatePropertyNameDto extends AbstractDto
{
    /** @var list<int> */
    #[Path('item_id')]
    public array|null $ids = [];

    /** @var list<int> */
    #[Path('item_id')]
    public array|null $otherIds = [];
}
