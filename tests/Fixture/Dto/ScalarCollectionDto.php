<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

class ScalarCollectionDto extends AbstractDto
{
    /**
     * @var list<int>
     */
    public array $ids = [];

    /**
     * @var list<string>
     */
    public array $tags = [];
}
