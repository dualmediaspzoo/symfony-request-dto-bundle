<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\Enum\Time as TimeEnum;

/**
 * Normalize the time-of-day of a parsed date. Composes with (and is independent of)
 * `#[Format]`; when present the parsed time is overwritten, when absent it is left as is.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class Time
{
    /**
     * @param TimeEnum|string $value a {@see TimeEnum} preset or a raw `H:i:s` time-of-day
     */
    public function __construct(
        public TimeEnum|string $value
    ) {
    }
}
