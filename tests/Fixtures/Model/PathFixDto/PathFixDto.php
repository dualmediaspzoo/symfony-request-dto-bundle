<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Path;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class PathFixDto extends AbstractDto
{
    #[Assert\NotNull]
    public int|null $integer = null;

    #[Path('other_string_path')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 200)]
    public string|null $string = null;
}
