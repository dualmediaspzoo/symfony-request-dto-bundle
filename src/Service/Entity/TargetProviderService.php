<?php

namespace DualMedia\DtoRequestBundle\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\Interface\Entity\TargetProviderInterface;
use DualMedia\DtoRequestBundle\Traits\Provider\MetadataTrait;

/**
 * @implements TargetProviderInterface<object>
 */
class TargetProviderService implements TargetProviderInterface
{
    use MetadataTrait;

    /**
     * @var EntityRepository<object>|null
     */
    private EntityRepository|null $repository = null;
    private EntityManagerInterface|null $manager = null;

    /**
     * @var class-string|null
     */
    private string|null $fqcn = null;

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly QueryCreator $creator
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
        $this->manager = $manager;
        $this->fqcn = $fqcn;

        return true;
    }

    #[\Override]
    public function findComplex(
        callable $fn,
        array $fields,
        array|null $orderBy = null,
        array $metadata = []
    ) {
        if (null === $this->repository) {
            return null;
        }

        return $fn($fields, $orderBy, $this->repository->createQueryBuilder('entity'), $metadata); // @phpstan-ignore-line
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

        if (!$this->metaAsReference($metadata)) {
            return $this->repository->findOneBy($criteria, $orderBy) ?? null;
        }

        return $this->resolveReferences(
            $this->creator->buildQuery(
                $this->repository->createQueryBuilder('entity'),
                'entity',
                $criteria,
                $orderBy ?? []
            )
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

        if (!$this->metaAsReference($metadata)) {
            return $this->repository->findBy($criteria, $orderBy, $limit, $offset); // @phpstan-ignore-line
        }

        return $this->resolveReferences(
            $this->creator->buildQuery(
                $this->repository->createQueryBuilder('entity'),
                'entity',
                $criteria,
                $orderBy ?? []
            )
        );
    }

    /**
     * @return list<object>
     */
    private function resolveReferences(
        QueryBuilder $qb
    ): array {
        assert(null !== $this->manager);
        assert(null !== $this->fqcn);

        $ids = $qb->select('entity.id')
            ->getQuery()
            ->getSingleColumnResult();

        $results = [];

        foreach ($ids as $id) {
            $results[] = $this->manager->getReference($this->fqcn, $id);
        }

        return array_values(array_filter($results));
    }
}
