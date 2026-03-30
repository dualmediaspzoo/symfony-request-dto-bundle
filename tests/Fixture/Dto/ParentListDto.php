<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\ObjectType;

class ParentListDto extends AbstractDto
{
    public string|null $name = null;

    /**
     * @var list<ScalarDto>
     */
    #[ObjectType(ScalarDto::class)]
    public array $children = [];
}
