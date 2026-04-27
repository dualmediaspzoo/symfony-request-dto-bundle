<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsRoot;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;

class AsRootWithParameterDto extends AbstractDto
{
    /**
     * @var list<ScalarDto>
     */
    #[AsRoot]
    public array $children = [];

    #[Bag(BagEnum::Attributes)]
    #[Path('weird')]
    public int|null $field = null;
}
