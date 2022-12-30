<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity;

use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderInterface;

class BadDummyModelProvider implements ProviderInterface
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
