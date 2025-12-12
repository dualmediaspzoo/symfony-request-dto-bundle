<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DualMedia\DtoRequestBundle\Attribute\Dto\FindBy;
use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\Validator\Constraints as Assert;

class MultiFindDto extends AbstractDto
{
    /**
     * @var DummyModel[]
     */
    #[FindBy(
        fields: ['id' => 'something'],
        constraints: ['id' => [
            new Assert\NotNull(),
            new Assert\Count(min: 1, max: 15),
        ]],
        types: ['id' => new Type('int', true)]
    )]
    public array $models = [];
}
