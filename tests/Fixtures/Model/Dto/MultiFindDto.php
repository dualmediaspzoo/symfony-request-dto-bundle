<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\FindBy;
use DM\DtoRequestBundle\Attributes\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
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
