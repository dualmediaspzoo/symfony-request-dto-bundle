<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class NestedBodyDto extends AbstractDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 32)]
    public string|null $name = null;

    #[Assert\GreaterThan(0)]
    public int|null $count = null;
}
