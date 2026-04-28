<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Service;

use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;

/**
 * Returns a custom group set without including `Default`. This is on purpose:
 * tests that exercise the bundle's "always-merge-Default" behavior would be
 * masked by `TestGroupProvider`, which seeds `['Default']`.
 */
class TestExtraGroupProvider implements GroupProviderInterface
{
    /**
     * @var list<string>
     */
    public array $groups = ['extra'];

    /**
     * @return list<string>
     */
    public function resolve(): array
    {
        return $this->groups;
    }
}
