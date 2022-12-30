<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DM\DtoRequestBundle\Attributes\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

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
