<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Time
{
    /**
     * @param \DateInterval $interval time-of-day as an offset from midnight, applied to a parsed date
     */
    public function __construct(
        public \DateInterval $interval
    ) {
    }
}
