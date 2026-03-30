<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\ObjectType;

class ParentCollectionDto extends AbstractDto
{
    public string|null $name = null;

    /**
     * @var Collection<int, ScalarDto>
     */
    #[ObjectType(ScalarDto::class)]
    public Collection $children;
}
