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

    #[Assert\NotBlank]
    #[Assert\Length(min: 8, max: 64)]
    #[Assert\Regex(pattern: '/[0-9!@#\$%^&*\(\),\.?":{}|<>_]+/', message: 'Minimum 1 digit or special glyph')]
    #[Assert\Regex(pattern: '/[a-z]/', message: 'Minimum 1 lowercase character')]
    #[Assert\Regex(pattern: '/[A-Z]/', message: 'Minimum 1 uppercase character')]
    public string|null $password = null;
}
