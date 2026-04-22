<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;

class EnumCollectionDto extends AbstractDto
{
    /**
     * @var list<StringBackedEnum>
     */
    public array $stringEnums = [];

    /**
     * @var list<IntBackedEnum>
     */
    public array $intEnums = [];

    /**
     * @var list<PureEnum>
     */
    #[FromKey]
    public array $pureEnumsByKey = [];
}
