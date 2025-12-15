<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\Interface\Entity\TargetProviderInterface;
use DualMedia\DtoRequestBundle\Util;

/**
 * @implements TargetProviderInterface<object>
 */
class TargetProviderService implements TargetProviderInterface
{
    /**
     * @var EntityRepository<object>|null
     */
    private EntityRepository|null $repository = null;

    /**
     * @var class-string|null
     */
    private string|null $fqcn = null;

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly QueryCreator $creator,
        private readonly ReferenceHelper $helper
    ) {
    }

    #[\Override]
    public function setFqcn(
        string $fqcn
    ): bool {
        // This is kinda nasty, but Doctrine will be changing the interface in 3.0 anyway, so it should be fine?
        $manager = $this->registry->getManagerForClass($fqcn);

        if (!($manager instanceof EntityManagerInterface)) {
            return false;
        }

        $this->repository = $manager->getRepository($fqcn);
        $this->fqcn = $fqcn;

        return true;
    }

    #[\Override]
    public function findComplex(
        callable $fn,
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
        array $metadata = []
    ) {
        if (null === $this->repository) {
            return null;
        }

        return $fn($criteria, $orderBy, $this->repository->createQueryBuilder('entity'), $limit, $offset, $metadata); // @phpstan-ignore-line
    }

    #[\Override]
    public function findOneBy(
        array $criteria,
        array|null $orderBy = null,
        array $metadata = []
    ): mixed {
        if (null === $this->repository) {
            return null;
        }

        if (!Util::metaHasReference($metadata)) {
            return $this->repository->findOneBy($criteria, $orderBy) ?? null;
        }

        assert(null !== $this->fqcn);

        return $this->helper->resolve(
            $this->creator->buildQuery(
                $this->repository->createQueryBuilder('entity'),
                'entity',
                $criteria,
                $orderBy ?? []
            ),
            $this->fqcn
        )[0] ?? null;
    }

    #[\Override]
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
        array $metadata = []
    ) {
        if (null === $this->repository) {
            return [];
        }

        if (!Util::metaHasReference($metadata)) {
            return $this->repository->findBy($criteria, $orderBy, $limit, $offset); // @phpstan-ignore-line
        }

        assert(null !== $this->fqcn);

        return $this->helper->resolve(
            $this->creator->buildQuery(
                $this->repository->createQueryBuilder('entity'),
                'entity',
                $criteria,
                $orderBy ?? [],
                $limit,
                $offset
            ),
            $this->fqcn
        );
    }
}
