<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\ObjectType;

class ComplexDto extends AbstractDto
{
    public int|null $someInput = null;

    public VerySimpleDto|null $verySimpleDto = null;

    /**
     * @var list<VerySimpleDto>
     */
    #[ObjectType(VerySimpleDto::class)]
    public array $listOfDto = [];
}
