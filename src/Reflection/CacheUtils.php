<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Symfony\Component\Validator\Constraint;

final class CacheUtils
{
    private function __construct()
    {
    }

    /**
     * @param list<Constraint> $constraints
     */
    public static function constraintsAreSerializable(
        array $constraints
    ): bool {
        if ([] === $constraints) {
            return true;
        }

        try {
            serialize($constraints);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
