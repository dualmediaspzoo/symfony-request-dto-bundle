<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentWithChildActionDto extends AbstractDto
{
    public int|null $parentValue = null;

    public ChildActionDto|null $child = null;
}
