<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Service\Entity;

use Doctrine\ORM\QueryBuilder;

class ReferenceHelper
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public function resolve(
        QueryBuilder $qb,
        string $class,
        string $idField = 'id'
    ): array {
        $manager = $qb->getEntityManager();

        $ids = $qb->select('entity.'.$idField)
            ->getQuery()
            ->getSingleColumnResult();

        $results = [];

        foreach ($ids as $id) {
            $results[] = $manager->getReference($class, $id);
        }

        return array_values(array_filter($results));
    }
}
