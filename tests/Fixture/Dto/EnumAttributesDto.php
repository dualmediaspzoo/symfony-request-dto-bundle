<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithAllowedEnum;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithLabelProcessor;
use DualMedia\DtoRequestBundle\Resolve\Label\PascalCaseProcessor;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\MultiWordEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;

class EnumAttributesDto extends AbstractDto
{
    #[WithAllowedEnum([IntBackedEnum::One])]
    public IntBackedEnum|null $intRestricted = null;

    #[WithAllowedEnum([StringBackedEnum::Foo])]
    public StringBackedEnum|null $stringRestricted = null;

    #[FromKey]
    #[WithLabelProcessor(PascalCaseProcessor::class)]
    public MultiWordEnum|null $multiWord = null;

    #[FromKey]
    #[WithLabelProcessor(PascalCaseProcessor::class)]
    #[WithAllowedEnum([MultiWordEnum::FirstCase])]
    public MultiWordEnum|null $multiWordRestricted = null;

    /**
     * @var list<IntBackedEnum>
     */
    #[WithAllowedEnum([IntBackedEnum::Two])]
    public array $intCollectionRestricted = [];
}
