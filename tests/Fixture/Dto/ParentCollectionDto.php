<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ParentCollectionDto extends AbstractDto
{
    public string|null $name = null;

    /**
     * @var Collection<int, ScalarDto>
     */
    public Collection $children;
}
