<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class ConstrainedScalarDto extends AbstractDto
{
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    public int|null $positiveInt = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 50)]
    public string|null $boundedString = null;

    #[Assert\NotNull]
    #[Assert\Range(min: 0.0, max: 1.0)]
    public float|null $ratio = null;
}
