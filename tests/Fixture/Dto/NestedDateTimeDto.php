<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class NestedDateTimeDto extends AbstractDto
{
    public string|null $label = null;

    public DateTimeDto|null $dates = null;
}
