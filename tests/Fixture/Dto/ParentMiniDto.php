<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentMiniDto extends AbstractDto
{
    public string|null $value = null;

    public MiniDto $child;
}
