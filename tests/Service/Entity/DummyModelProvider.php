<?php

namespace DualMedia\DtoRequestBundle\Tests\Service\Entity;

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
        // TODO: Implement findComplex() method.
    }

    public function findOneBy(
        array $criteria,
        ?array $orderBy = null
    ) {
        // TODO: Implement findOneBy() method.
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        // TODO: Implement findBy() method.
    }
}
