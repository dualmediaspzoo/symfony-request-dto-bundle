<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
readonly class AsDynamicProvider
{
    /**
     * @param non-empty-string|list<non-empty-string> $parameter
     */
    public function __construct(
        public string|array $parameter
    ) {
    }
}
