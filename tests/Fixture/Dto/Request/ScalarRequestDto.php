<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Request;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ScalarRequestDto extends AbstractDto
{
    #[Bag(BagEnum::Query)]
    #[Assert\NotBlank]
    public string|null $name = null;
}
