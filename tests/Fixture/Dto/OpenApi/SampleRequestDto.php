<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class SampleRequestDto extends AbstractDto
{
    #[Bag(BagEnum::Attributes)]
    #[Assert\NotNull]
    public int|null $id = null;

    #[Bag(BagEnum::Query)]
    #[Assert\Length(min: 3, max: 10)]
    public string|null $search = null;

    #[Assert\NotBlank]
    public NestedBodyDto|null $nested = null;

    public StringBackedEnum|null $status = null;

    #[Bag(BagEnum::Files)]
    public UploadedFile|null $avatar = null;
}
