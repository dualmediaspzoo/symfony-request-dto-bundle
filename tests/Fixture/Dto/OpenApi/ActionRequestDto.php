<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Action;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

class ActionRequestDto extends AbstractDto
{
    /**
     * Top-level thing description.
     *
     * Continues here.
     */
    #[Action(when: ActionCondition::Null, statusCode: 404, description: 'Thing not found')]
    public int|null $thing = null;

    public NestedActionDto|null $nested = null;
}
