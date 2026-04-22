<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\TypeIdentifier;

final class TypeMapper
{
    private function __construct()
    {
    }

    /**
     * Maps a Symfony TypeInfo Type to an OpenAPI primitive schema type.
     * For collections, returns the mapped inner value type (or 'string' as a safe default).
     */
    public static function toOpenApi(
        Type $type
    ): string {
        if (TypeInfoUtils::isCollection($type)) {
            $inner = TypeInfoUtils::getCollectionValueType($type);

            return null !== $inner ? self::toOpenApi($inner) : 'string';
        }

        if ($type->isIdentifiedBy(TypeIdentifier::INT)) {
            return 'integer';
        }

        if ($type->isIdentifiedBy(TypeIdentifier::FLOAT)) {
            return 'number';
        }

        if ($type->isIdentifiedBy(TypeIdentifier::BOOL)) {
            return 'boolean';
        }

        if ($type->isIdentifiedBy(TypeIdentifier::STRING)) {
            return 'string';
        }

        if ($type->isIdentifiedBy(\DateTimeInterface::class)
            || $type->isIdentifiedBy(\DateTimeImmutable::class)) {
            return 'string';
        }

        if ($type->isIdentifiedBy(UploadedFile::class)) {
            return 'string';
        }

        if (self::isBackedEnum($type)) {
            return self::enumBackingType($type);
        }

        $className = TypeInfoUtils::getClassName($type);

        if (null !== $className && is_subclass_of($className, AbstractDto::class)) {
            return 'object';
        }

        return 'object';
    }

    public static function isUploadedFile(
        Type $type
    ): bool {
        if (TypeInfoUtils::isCollection($type)) {
            $inner = TypeInfoUtils::getCollectionValueType($type);

            return null !== $inner && self::isUploadedFile($inner);
        }

        return $type->isIdentifiedBy(UploadedFile::class);
    }

    public static function isBackedEnum(
        Type $type
    ): bool {
        $backed = false;

        $type->isSatisfiedBy(static function (Type $t) use (&$backed): bool {
            if ($t instanceof EnumType) {
                $backed = is_subclass_of($t->getClassName(), \BackedEnum::class);

                return true;
            }

            return false;
        });

        return $backed;
    }

    /**
     * @return class-string<\BackedEnum>|null
     */
    public static function backedEnumClass(
        Type $type
    ): string|null {
        $className = null;

        $type->isSatisfiedBy(static function (Type $t) use (&$className): bool {
            if ($t instanceof EnumType) {
                if (is_subclass_of($candidate = $t->getClassName(), \BackedEnum::class)) {
                    $className = $candidate;

                    return true;
                }
            }

            return false;
        });

        return $className;
    }

    private static function enumBackingType(
        Type $type
    ): string {
        $class = self::backedEnumClass($type);

        if (null === $class) {
            return 'string';
        }

        $backingType = new \ReflectionEnum($class)->getBackingType();

        if (null !== $backingType && 'int' === $backingType->getName()) {
            return 'integer';
        }

        return 'string';
    }
}
