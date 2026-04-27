<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class MultiCountGroupsDto extends AbstractDto
{
    /**
     * @var list<int>
     */
    #[Assert\Count(min: 1, groups: ['full'])]
    #[Assert\Count(max: 10)]
    public array|null $field = [];

    /**
     * @var list<int>
     */
    #[Assert\Count(min: 1, max: 5)]
    public array $defaultMin = [];
}