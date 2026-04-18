<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Action;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

class NestedActionDto extends AbstractDto
{
    #[Action(when: ActionCondition::Null, statusCode: 403, description: 'Nested forbidden')]
    public int|null $nestedValue = null;
}
