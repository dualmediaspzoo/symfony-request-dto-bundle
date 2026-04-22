<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

final class CacheUtils
{
    private function __construct()
    {
    }

    /**
     * @param list<mixed> $items
     */
    public static function isSerializable(
        array $items
    ): bool {
        if (empty($items)) {
            return true;
        }

        try {
            serialize($items);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
