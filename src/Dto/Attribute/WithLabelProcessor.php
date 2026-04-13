<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

/**
 * To be used together with {@link FromKey}.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class WithLabelProcessor
{
    public function __construct(
        public string $serviceId
    ) {
    }
}
