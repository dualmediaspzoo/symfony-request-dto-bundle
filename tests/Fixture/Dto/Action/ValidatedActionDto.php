<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Action;
use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;
use Symfony\Component\Validator\Constraints as Assert;

class ValidatedActionDto extends AbstractDto
{
    #[Assert\NotBlank]
    #[Action(when: ActionCondition::Null, statusCode: 404, description: 'Order not found')]
    public string|null $value = null;
}
