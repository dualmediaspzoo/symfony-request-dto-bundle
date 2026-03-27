<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class VerySimpleDto extends AbstractDto
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(15)]
    public int|null $intField = null;

    #[Assert\NotBlank]
    public string|null $stringField = null;

    public \DateTimeInterface|null $dateTime = null;
}
