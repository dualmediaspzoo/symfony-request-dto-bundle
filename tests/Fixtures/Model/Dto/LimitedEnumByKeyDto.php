<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\PascalCaseProcessor;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;

class LimitedEnumByKeyDto extends AbstractDto
{
    #[FromKey]
    #[AllowEnum([IntegerEnum::IntegerKey])]
    public IntegerEnum|null $int = null;

    #[FromKey(PascalCaseProcessor::class)]
    #[AllowEnum([StringEnum::StringKey])]
    public StringEnum|null $string = null;
}
