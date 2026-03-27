<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;

/**
 * Loading logic for specifying non-default entity/object providers.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Provider
{
    /**
     * @template T of object
     * @param \Closure(ProviderInterface<T>): (T|list<T>) $closure
     */
    public function __construct(
        public \Closure $closure
    ) {
    }
}
