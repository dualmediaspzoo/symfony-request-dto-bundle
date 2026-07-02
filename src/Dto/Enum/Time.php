<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Enum;

/**
 * Convenience presets for the optional time normalization of #[Format].
 *
 * The backed value is an `H:i:s` time-of-day applied to a parsed date. Any
 * other valid `H:i:s` string may be passed to #[Format] directly instead of
 * one of these cases.
 */
enum Time: string
{
    case StartOfDay = '00:00:00';
    case Midday = '12:00:00';
    case EndOfDay = '23:59:59';
}
