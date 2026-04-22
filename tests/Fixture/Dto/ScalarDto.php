<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ScalarDto extends AbstractDto
{
    public int|null $intField = null;

    public string|null $stringField = null;

    public float|null $floatField = null;

    public bool|null $boolField = null;
}
