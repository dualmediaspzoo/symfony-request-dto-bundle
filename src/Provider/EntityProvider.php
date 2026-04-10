<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Doctrine\ORM\EntityRepository;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;
use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;
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
        private readonly EntityRepository $repository
    ) {
    }

    public function find(
        array $criteria,
        array $metadata = []
    ): mixed {
        $asReference = MetadataUtils::exists(AsDoctrineReference::class, $metadata);
        $find = MetadataUtils::single(FindBy::class, $metadata);
        assert(null !== $find);

        $orderBy = [];

        foreach (MetadataUtils::list(OrderBy::class, $metadata) as $item) {
            $orderBy[$item->field] = $item->order;
        }

        $limit = MetadataUtils::single(Limit::class, $metadata)?->count;
        $offset = MetadataUtils::single(Offset::class, $metadata)?->count;

        if (!$asReference) {
            return $find->many
                ? $this->repository->findBy($criteria, $orderBy, $limit, $offset) // @phpstan-ignore-line
                : $this->repository->findOneBy($criteria, $orderBy);
        }

        throw new \LogicException('Not yet implemented');

        // resolve reference
        //        return $this->helper->resolve(
        //            $this->creator->buildQuery(
        //                $this->repository->createQueryBuilder('entity'),
        //                'entity',
        //                $criteria,
        //                $orderBy ?? []
        //            ),
        //            $this->fqcn
        //        )[0] ?? null;
    }
}
