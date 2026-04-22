<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentListDto extends AbstractDto
{
    public string|null $name = null;

    /**
     * @var list<ScalarDto>
     */
    public array $children = [];
}
