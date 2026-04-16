<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Util;

use DualMedia\DtoRequestBundle\Dto\Enum\ActionCondition;

class ActionConditionUtils
{
    public const \Closure NULL = static fn (mixed $v): bool => null === $v;

    public const \Closure EMPTY = static fn (mixed $v): bool => empty($v);

    public const \Closure FALSE = static fn (mixed $v): bool => false === $v;

    private function __construct()
    {
    }

    public static function resolve(
        ActionCondition $condition
    ): \Closure {
        return match ($condition) {
            ActionCondition::Null => self::NULL,
            ActionCondition::Empty => self::EMPTY,
            ActionCondition::False => self::FALSE,
        };
    }
}
