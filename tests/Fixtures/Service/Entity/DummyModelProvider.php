<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DualMedia\DtoRequestBundle\Attribute\Entity\EntityProvider;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

/**
 * @see DummyModel
 *
 * @implements ProviderInterface<DummyModel>
 */
#[EntityProvider(DummyModel::class, true)]
class DummyModelProvider implements ProviderInterface
{
    public function findComplex(
        callable $fn,
        array $fields,
        array|null $orderBy = null
    ) {
        throw new \RuntimeException('Not implemented');
    }

    public function findOneBy(
        array $criteria,
        array|null $orderBy = null
    ) {
        throw new \RuntimeException('Not implemented');
    }

    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null
    ): array {
        throw new \RuntimeException('Not implemented');
    }
}
