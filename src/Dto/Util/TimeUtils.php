<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Util;

use DualMedia\DtoRequestBundle\Dto\Enum\Time;

class TimeUtils
{
    /**
     * @throws \InvalidArgumentException when `$time` is not a valid `H:i:s` value
     */
    public static function toInterval(
        Time|string $time
    ): \DateInterval {
        $value = $time instanceof Time ? $time->value : $time;

        if (false === ($parsed = \DateTimeImmutable::createFromFormat('!H:i:s', $value))) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid time "%s" passed to #[Time], expected an "H:i:s" time-of-day.',
                $value
            ));
        }

        return new \DateInterval(sprintf(
            'PT%dH%dM%dS',
            (int)$parsed->format('G'),
            (int)$parsed->format('i'),
            (int)$parsed->format('s')
        ));
    }
}
