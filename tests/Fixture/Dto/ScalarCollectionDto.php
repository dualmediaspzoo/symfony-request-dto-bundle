<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Type;

class ScalarCollectionDto extends AbstractDto
{
    /**
     * @var list<int>
     */
    #[Type('int')]
    public array $ids = [];

    /**
     * @var list<string>
     */
    #[Type('string')]
    public array $tags = [];
}
