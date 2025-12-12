<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\Interface\Entity\TargetProviderInterface;

/**
 * @implements TargetProviderInterface<object>
 */
class TargetProviderService implements TargetProviderInterface
{
    private EntityRepository|null $repository = null;

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    #[\Override]
    public function setFqcn(
        string $fqcn
    ): bool {
        // This is kinda nasty, but Doctrine will be changing the interface in 3.0 anyway, so it should be fine?
        return null !== (
            $this->repository = $this->registry->getManagerForClass($fqcn)?->getRepository($fqcn) ?? null // @phpstan-ignore-line
        );
    }

    #[\Override]
    public function findComplex(
        callable $fn,
        array $fields,
        array|null $orderBy = null
    ) {
        if (null === $this->repository) {
            return null;
        }

        return $fn($fields, $orderBy, $this->repository->createQueryBuilder('entity')); // @phpstan-ignore-line
    }

    #[\Override]
    public function findOneBy(
        array $criteria,
        array|null $orderBy = null
    ) {
        return $this->repository?->findOneBy($criteria, $orderBy) ?? null;
    }

    #[\Override]
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null
    ) {
        return $this->repository?->findBy($criteria, $orderBy, $limit, $offset) ?? []; // @phpstan-ignore-line
    }
}
