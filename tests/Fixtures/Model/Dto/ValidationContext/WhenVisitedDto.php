<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Constraint\WhenVisited;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints\NotNull;

class WhenVisitedDto extends AbstractDto
{
    #[WhenVisited([new NotNull()])]
    public string|null $nullableValue = null;
}
