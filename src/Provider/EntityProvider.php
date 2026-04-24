<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Doctrine\ORM\EntityRepository;
use DualMedia\DoctrineQueryCreator\QueryCreator;
use DualMedia\DoctrineQueryCreator\ReferenceHelper;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\Interface\StandardObjectProviderInterface;

/**
 * @implements StandardObjectProviderInterface<object>
 */
class EntityProvider implements StandardObjectProviderInterface
{
    /**
     * @param class-string $class
     * @param EntityRepository<object> $repository
     */
    public function __construct(
        private readonly string $class,
        private readonly EntityRepository $repository,
        private readonly QueryCreator $queryCreator,
        private readonly ReferenceHelper $referenceHelper
    ) {
    }

    public function find(
        array $criteria,
        array $metadata = []
    ): mixed {
        $asReference = MetadataUtils::exists(AsDoctrineReference::class, $metadata);
        $find = MetadataUtils::single(FindBy::class, $metadata);
        assert(null !== $find);

        $orderBy = MetadataUtils::orderBy($metadata);

        $limit = MetadataUtils::single(Limit::class, $metadata)?->count;
        $offset = MetadataUtils::single(Offset::class, $metadata)?->count;

        if (!$asReference) {
            return $find->many
                ? $this->repository->findBy($criteria, $orderBy, $limit, $offset) // @phpstan-ignore-line
                : $this->repository->findOneBy($criteria, $orderBy);
        }

        $references = $this->referenceHelper->resolve(
            $this->queryCreator->build(
                $this->repository->createQueryBuilder('entity'),
                'entity',
                $criteria,
                $orderBy,
                $limit,
                $offset
            ),
            $this->class
        );

        return $find->many ? $references[0] ?? null : $references;
    }
}
