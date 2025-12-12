<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class ComplexDto extends AbstractDto
{
    public int|null $myInt = null;

    public string|null $myString = null;

    /**
     * @var int[]
     */
    public array $intArr = [];

    #[FindOneBy(
        fields: ['id' => 'id', 'custom' => '$customProp', 'date' => 'whatever'],
        types: ['id' => new Type('int'), 'date' => new Type('datetime')]
    )]
    public DummyModel|null $model = null;

    public SubDto|null $dto = null;

    public \DateTimeImmutable|null $date = null;

    public IntegerEnum|null $intEnum = null;

    public StringEnum|null $stringEnum = null;
}
