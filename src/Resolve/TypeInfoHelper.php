<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\NullableType;
use Symfony\Component\TypeInfo\Type\ObjectType;

final class TypeInfoHelper
{
    public static function unwrap(
        Type $type
    ): Type {
        return $type instanceof NullableType ? $type->getWrappedType() : $type;
    }

    public static function getClassName(
        Type $type
    ): string|null {
        $inner = self::unwrap($type);

        if ($inner instanceof ObjectType) {
            return $inner->getClassName();
        }

        return null;
    }

    public static function isCollection(
        Type $type
    ): bool {
        return self::unwrap($type) instanceof CollectionType;
    }

    public static function isDoctrineCollection(
        Type $type
    ): bool {
        $inner = self::unwrap($type);

        if (!$inner instanceof CollectionType) {
            return false;
        }

        $wrapped = $inner->getWrappedType();

        if ($wrapped instanceof GenericType) {
            $wrapped = $wrapped->getWrappedType();
        }

        return $wrapped instanceof ObjectType
            && is_a($wrapped->getClassName(), Collection::class, true);
    }

    public static function getCollectionValueType(
        Type $type
    ): Type|null {
        $inner = self::unwrap($type);

        if (!$inner instanceof CollectionType) {
            return null;
        }

        return $inner->getCollectionValueType();
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
