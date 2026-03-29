<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Dto\Attribute\ObjectType;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraints as Assert;

class ComplexDto extends AbstractDto
{
    #[Path('some-path')]
    public int|null $someInput = null;

    #[Assert\NotBlank]
    public VerySimpleDto|null $verySimpleDto = null;

    /**
     * @var list<VerySimpleDto>
     */
    #[ObjectType(VerySimpleDto::class)]
    public array $listOfDto = [];
}
