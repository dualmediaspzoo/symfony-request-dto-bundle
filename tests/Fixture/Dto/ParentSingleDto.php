<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentSingleDto extends AbstractDto
{
    public string|null $name = null;

    public ScalarDto|null $child = null;
}
