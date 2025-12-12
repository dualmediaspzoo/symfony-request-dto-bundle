<?php

namespace DualMedia\DtoRequestBundle\Tests\Service\Entity;

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
        // TODO: Implement findComplex() method.
    }

    public function findOneBy(
        array $criteria,
        array|null $orderBy = null
    ) {
        // TODO: Implement findOneBy() method.
    }

    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null
    ): array {
        // TODO: Implement findBy() method.
    }
}
