<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto;

use DM\DtoRequestBundle\Attributes\Dto\Path;
use DM\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class PathFixDto extends AbstractDto
{
    #[Assert\NotNull]
    public ?int $integer = null;

    #[Path('other_string_path')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10, max: 200)]
    public ?string $string = null;
}
