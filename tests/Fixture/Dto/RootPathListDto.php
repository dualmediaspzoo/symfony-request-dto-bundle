<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsRoot;

class RootPathListDto extends AbstractDto
{
    /**
     * @var list<ScalarDto>
     */
    #[AsRoot]
    public array $items = [];
}
