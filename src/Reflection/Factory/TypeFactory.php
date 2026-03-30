<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection\Factory;

use DualMedia\DtoRequestBundle\Dto\Attribute\Type as TypeAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;

class TypeFactory
{
    /**
     * Creates a Type from a ReflectionNamedType, normalizing non-builtin types
     * to 'object' with the original name as fqcn.
     *
     * When a TypeAttribute is provided, its type and fqcn override the reflected values.
     */
    public function type(
        \ReflectionNamedType $reflectionType,
        string|null $collectionType = null,
        TypeAttribute|null $typeAttribute = null
    ): Type {
        if (null !== $typeAttribute) {
            return new Type($typeAttribute->type, $collectionType, $typeAttribute->fqcn);
        }

        $typeName = $reflectionType->getName();

        if (!$reflectionType->isBuiltin()) {
            return new Type('object', $collectionType, $typeName);
        }

        return new Type($typeName, $collectionType);
    }

    /**
     * Creates a Type from a Type attribute, or null when no attribute is present.
     */
    public function typeFromAttribute(
        TypeAttribute|null $attribute
    ): Type|null {
        if (null === $attribute) {
            return null;
        }

        return new Type($attribute->type, null, $attribute->fqcn);
    }

    public function default(): Type
    {
        return new Type('string', null);
    }
}
