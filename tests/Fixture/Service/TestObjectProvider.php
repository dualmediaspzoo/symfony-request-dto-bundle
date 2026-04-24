<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Service;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;

/**
 * @implements ProviderInterface<\stdClass>
 */
class TestObjectProvider implements ProviderInterface
{
    /**
     * @var array<int|string, \stdClass>
     */
    public array $store = [];

    /**
     * @var list<array{criteria: array<string, mixed>, meta: list<object>}>
     */
    public array $calls = [];

    /**
     * @param array<string, mixed> $criteria
     * @param list<object> $meta
     */
    public function find(
        array $criteria,
        array $meta
    ): \stdClass|null {
        $this->calls[] = ['criteria' => $criteria, 'meta' => $meta];

        $id = $criteria['id'] ?? null;

        if (!is_scalar($id)) {
            return null;
        }

        return $this->store[(string)$id] ?? null;
    }
}
