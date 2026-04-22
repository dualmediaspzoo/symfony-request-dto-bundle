<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Action;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

class SimpleActionDto extends AbstractDto
{
    #[Action(when: ActionCondition::Null, statusCode: 404)]
    public int|null $value = null;
}
