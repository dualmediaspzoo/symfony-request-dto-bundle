<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;

class EnumDto extends AbstractDto
{
    public IntBackedEnum|null $intEnum = null;

    #[FromKey]
    public IntBackedEnum|null $intEnumByKey = null;

    public StringBackedEnum|null $stringEnum = null;

    #[FromKey]
    public StringBackedEnum|null $stringEnumByKey = null;

    #[FromKey]
    public PureEnum|null $pureEnumByKey = null;

    public PureEnum|null $pureEnumInvalid = null;
}
