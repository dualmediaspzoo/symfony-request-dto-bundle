<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DualMedia\DtoRequestBundle\Attributes\Entity\EntityProvider;
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
        ?array $orderBy = null
    ) {
        throw new \RuntimeException("Not implemented");
    }

    public function findOneBy(
        array $criteria,
        ?array $orderBy = null
    ) {
        throw new \RuntimeException("Not implemented");
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        throw new \RuntimeException("Not implemented");
    }
}
