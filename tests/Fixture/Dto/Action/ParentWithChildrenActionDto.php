<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto\Action;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentWithChildrenActionDto extends AbstractDto
{
    public int|null $parentValue = null;

    /**
     * @var list<ChildActionDto>
     */
    public array $children = [];
}
