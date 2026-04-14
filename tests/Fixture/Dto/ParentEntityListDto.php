<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class ParentEntityListDto extends AbstractDto
{
    public string|null $name = null;

    /**
     * @var list<PropertyAssertFindDto>
     */
    #[Assert\Valid]
    public array $children = [];
}
