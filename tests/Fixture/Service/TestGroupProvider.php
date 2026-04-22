<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Service;

use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;

class TestGroupProvider implements GroupProviderInterface
{
    /**
     * @var list<string>
     */
    public array $groups = ['Default'];

    /**
     * @return list<string>
     */
    public function resolve(): array
    {
        return $this->groups;
    }
}
