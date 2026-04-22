<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Type;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\ObjectType;

final class TypeInfoUtils
{
    public static function getClassName(
        Type $type
    ): string|null {
        $className = null;

        $type->isSatisfiedBy(static function (Type $t) use (&$className): bool {
            if ($t instanceof ObjectType) {
                $className = $t->getClassName();

                return true;
            }

            return false;
        });

        return $className;
    }

    public static function isCollection(
        Type $type
    ): bool {
        return $type->isSatisfiedBy(static fn (Type $t): bool => $t instanceof CollectionType);
    }

    public static function isDoctrineCollection(
        Type $type
    ): bool {
        return $type->isSatisfiedBy(static fn (Type $t): bool => $t instanceof CollectionType
            && $t->isSatisfiedBy(static fn (Type $inner): bool => $inner instanceof ObjectType
                && is_a($inner->getClassName(), Collection::class, true)));
    }

    public static function getCollectionValueType(
        Type $type
    ): Type|null {
        $valueType = null;

        $type->isSatisfiedBy(static function (Type $t) use (&$valueType): bool {
            if ($t instanceof CollectionType) {
                $valueType = $t->getCollectionValueType();

                return true;
            }

            return false;
        });

        return $valueType;
    }

    public static function getCollectionValueClassName(
        Type $type
    ): string|null {
        $valueType = self::getCollectionValueType($type);

        if (null === $valueType) {
            return null;
        }

        return self::getClassName($valueType);
    }
}
