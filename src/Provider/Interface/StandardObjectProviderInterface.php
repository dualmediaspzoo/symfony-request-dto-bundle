<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Interface;

/**
 * @template T of object
 *
 * Interface for looking up entities with an actual implementation.
 * Mostly used for autoconfigured entity logic.
 *
 * @phpstan-import-type MetaFindModel from ProviderInterface
 * @phpstan-import-type FoundReturnType from ProviderInterface
 * @phpstan-import-type FindCriteria from ProviderInterface
 *
 * @extends ProviderInterface<T>
 */
interface StandardObjectProviderInterface extends ProviderInterface
{
    /**
     * @param FindCriteria $criteria
     * @param list<MetaFindModel> $metadata
     *
     * @return FoundReturnType
     */
    public function find(
        array $criteria,
        array $metadata = []
    ): mixed;
}
