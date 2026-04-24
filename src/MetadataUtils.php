<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle;

use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;

class MetadataUtils
{
    private function __construct()
    {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param list<object> $meta
     *
     * @return T|null
     */
    public static function single(
        string $class,
        array $meta
    ): object|null {
        return array_find($meta, static fn ($o) => $o instanceof $class);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param list<object> $meta
     */
    public static function exists(
        string $class,
        array $meta
    ): bool {
        return null !== self::single($class, $meta);
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param list<object> $meta
     *
     * @return list<T>
     */
    public static function list(
        string $class,
        array $meta
    ): array {
        return array_values(
            array_filter(
                $meta,
                static fn ($o) => $o instanceof $class
            )
        );
    }

    /**
     * Helper method for converting {@link OrderBy} metadata to Doctrine fields.
     *
     * @param list<object> $meta
     *
     * @return array<string, string>
     */
    public static function orderBy(
        array $meta
    ): array {
        $orderBy = [];

        foreach (MetadataUtils::list(OrderBy::class, $meta) as $item) {
            $orderBy[$item->field] = $item->order;
        }

        return $orderBy;
    }
}
