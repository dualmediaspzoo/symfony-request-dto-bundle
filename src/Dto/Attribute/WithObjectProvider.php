<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Mark entity for use with a custom provider.
 *
 * Provider will be resolved by autowiring by FQCN or {@link Autowire} on argument.
 *
 * @template T of object
 * @template TProvider of ProviderInterface<T>
 *
 * @phpstan-import-type MetaFindModel from ProviderInterface
 * @phpstan-import-type FoundReturnType from ProviderInterface
 *
 * @phpstan-type CustomProviderClosureFull \Closure(TProvider, array<string, mixed>, list<MetaFindModel>): FoundReturnType
 * @phpstan-type CustomProviderClosureCriteria \Closure(TProvider, array<string, mixed>): FoundReturnType
 * @phpstan-type CustomProviderClosureProviderOnly \Closure(TProvider): FoundReturnType
 * @phpstan-type CustomProviderClosure CustomProviderClosureFull|CustomProviderClosureCriteria|CustomProviderClosureProviderOnly
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class WithObjectProvider
{
    /**
     * @param CustomProviderClosure $closure
     */
    public function __construct(
        public \Closure $closure
    ) {
    }
}
