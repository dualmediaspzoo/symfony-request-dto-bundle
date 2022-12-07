<?php

namespace DM\DtoRequestBundle\Interfaces\Entity;

use DM\DtoRequestBundle\Annotations\Entity\EntityProvider;

/**
 * This interface may be implemented by classes that wish to provide entities to {@link DtoInterface} objects
 *
 * Add {@link EntityProvider} annotation on the class implementing
 *
 * @template T
 */
interface ProviderInterface
{
    /**
     * Load one or more objects through a callable
     *
     * Input and output to the callable may change depending on the provider and may not adhere
     * to the basic implementation suggested in this interface
     *
     * Proper arguments should be passed depending on the type of provider
     *
     * @param callable $fn
     * @param array<string, mixed> $fields
     * @psalm-param callable(array $fields, array $orderBy, mixed ...$args) $fn $orderBy must be nullable, but psalm doesn't like that
     * @param array<string, string>|null $orderBy
     *
     * @return T[]|T|null
     * @psalm-return list<T>|T|null
     *
     * @see QueryBuilder
     */
    public function findComplex(
        callable $fn,
        array $fields,
        ?array $orderBy = null
    );

    /**
     * Find one or no entities, must be compatible with Doctrine's EntityRepository::findOneBy
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     *
     * @return T|null
     */
    public function findOneBy(
        array $criteria,
        ?array $orderBy = null
    );

    /**
     * Find one or more entities, must be compatible with Doctrine's EntityRepository::findBy
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return list<T>
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    );
}
