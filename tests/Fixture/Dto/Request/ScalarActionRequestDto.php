<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Request;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Action;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;

class ScalarActionRequestDto extends AbstractDto
{
    #[Bag(BagEnum::Query)]
    #[Action(when: ActionCondition::Null, statusCode: 404)]
    public string|null $value = null;
}
