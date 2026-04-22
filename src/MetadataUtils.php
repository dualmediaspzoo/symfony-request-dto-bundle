<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle;

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
}

//    public function fixPropertyPath(
//        PropertyPath $propertyPath,
//        PropertyPathBuilder|null $builder = null,
//        int $index = 0
//    ): string {
//        $builder ??= new PropertyPathBuilder($propertyPath);
//        $jump = $this->isCollection() ? 2 : 1; // array index counts as a position in path
//
//        try {
//            $next = $propertyPath->getElement($jump);
//        } catch (OutOfBoundsException) {
//            $next = null;
//        }
//
//        if (null !== ($find = $this->getFindAttribute())) {
//            $builder->replaceByProperty(
//                $index,
//                $find->getErrorPath() ?? $find->getFirstNonDynamicField() ?? $this->getName()
//            );
//        } elseif (null !== $this->getPath()) {
//            $builder->replaceByProperty($index, $this->getPath());
//        }
//        $index += $jump;
//
//        // we want to exit if there's no next jump, we're hitting the index or the next element does not exist (custom path)
//        if (null === $next || $index === $builder->getLength() || !isset($this[$next])) {
//            return (string)$builder;
//        }
//
//        // we need to jump to the next property, which is always here as the actual prop name
//        return $this[$next]->fixPropertyPath($propertyPath, $builder, $index);
//    }
