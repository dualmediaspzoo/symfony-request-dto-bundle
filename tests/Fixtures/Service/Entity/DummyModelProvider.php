<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DualMedia\DtoRequestBundle\Attribute\Entity\EntityProvider;
use DualMedia\DtoRequestBundle\Interface\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

/**
 * @see DummyModel
 *
 * @implements ProviderInterface<DummyModel>
 */
#[EntityProvider(DummyModel::class, true)]
class DummyModelProvider implements ProviderInterface
{
    #[\Override]
    public function findComplex(
        callable $fn,
        array $fields,
        array|null $orderBy = null,
        array $metadata = []
    ) {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function findOneBy(
        array $criteria,
        array|null $orderBy = null,
        array $metadata = []
    ): mixed {
        throw new \RuntimeException('Not implemented');
    }

    #[\Override]
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
        array $metadata = []
    ): array {
        throw new \RuntimeException('Not implemented');
    }
}
