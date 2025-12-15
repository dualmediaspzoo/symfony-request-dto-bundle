<?php

namespace DualMedia\DtoRequestBundle\Interface\Entity;

use Doctrine\ORM\QueryBuilder;
use DualMedia\DtoRequestBundle\Attribute\Entity\EntityProvider;
use DualMedia\DtoRequestBundle\Interface\Attribute\DtoFindMetaAttributeInterface;

/**
 * This interface may be implemented by classes that wish to provide entities to {@link DtoInterface} objects.
 *
 * Add {@link EntityProvider} annotation on the class implementing this interface
 *
 * @template T of object
 */
interface ProviderInterface
{
    /**
     * Load one or more objects through a callable.
     *
     * Input and output to the callable may change depending on the provider and may not adhere
     * to the basic implementation suggested in this interface
     *
     * Proper arguments should be passed depending on the type of provider
     *
     * @param callable(array<string, mixed>, array<string, string>|null, QueryBuilder, list<DtoFindMetaAttributeInterface>): mixed $fn
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param list<DtoFindMetaAttributeInterface> $metadata
     *
     * @return list<T>|T|null
     *
     * @see QueryBuilder
     */
    public function findComplex(
        callable $fn,
        array $criteria,
        array|null $orderBy = null,
        array $metadata = []
    );

    /**
     * Find one or no entities.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param list<DtoFindMetaAttributeInterface> $metadata
     *
     * @return T|null
     */
    public function findOneBy(
        array $criteria,
        array|null $orderBy = null,
        array $metadata = []
    ): mixed;

    /**
     * Find one or more entities.
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param list<DtoFindMetaAttributeInterface> $metadata
     *
     * @return list<T>
     *
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function findBy(
        array $criteria,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null,
        array $metadata = []
    );
}
