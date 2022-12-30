<?php

namespace DM\DtoRequestBundle\Tests\Fixtures\Model\Dto;

use DM\DtoRequestBundle\Attributes\Dto\FindOneBy;
use DM\DtoRequestBundle\Attributes\Dto\Type;
use DM\DtoRequestBundle\Model\AbstractDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use Symfony\Component\Validator\Constraints as Assert;

class FindWithSecondErrorDto extends AbstractDto
{
    #[FindOneBy(
        fields: ['id' => 'something_id'],
        constraints: ['id' => new Assert\NotBlank()],
        types: ['id' => new Type('int')]
    )]
    public ?DummyModel $model = null;
}
