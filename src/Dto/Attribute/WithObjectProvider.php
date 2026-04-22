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
 * @phpstan-import-type CustomProviderClosure from ProviderInterface
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
