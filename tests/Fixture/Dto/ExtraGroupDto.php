<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class ExtraGroupDto extends AbstractDto
{
    #[Assert\Positive(groups: ['extra'])]
    #[Assert\NotNull]
    public int|null $value = null;
}