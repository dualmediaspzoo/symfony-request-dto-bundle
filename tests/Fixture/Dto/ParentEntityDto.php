<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class ParentEntityDto extends AbstractDto
{
    public string|null $name = null;

    #[Assert\Valid]
    public PropertyAssertFindDto|null $child = null;
}
