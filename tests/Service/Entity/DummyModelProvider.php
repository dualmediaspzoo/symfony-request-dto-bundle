<?php

namespace DM\DtoRequestBundle\Tests\Service\Entity;

use DM\DtoRequestBundle\Attributes\Entity\EntityProvider;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;

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
