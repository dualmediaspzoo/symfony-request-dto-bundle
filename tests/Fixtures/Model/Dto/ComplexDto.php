<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DualMedia\DtoRequestBundle\Attributes\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

class ComplexDto extends AbstractDto
{
    public ?int $myInt = null;

    public ?string $myString = null;

    /**
     * @var int[]
     */
    public array $intArr = [];

    #[FindOneBy(
        fields: ['id' => 'id', 'custom' => '$customProp', 'date' => 'whatever'],
        types: ['id' => new Type('int'), 'date' => new Type('datetime')]
    )]
    public ?DummyModel $model = null;

    public ?SubDto $dto = null;

    public ?\DateTimeImmutable $date = null;

    public ?IntegerEnum $intEnum = null;

    public ?StringEnum $stringEnum = null;
}
