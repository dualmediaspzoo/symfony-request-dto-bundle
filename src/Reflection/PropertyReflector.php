<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

class PropertyReflector
{
    public function reflect(
        \ReflectionProperty $property
    ): \ReflectionNamedType {
        $type = $property->getType();

        $throw = static fn (string $message) => throw new \LogicException(sprintf(
            $message,
            $property->getDeclaringClass()->getName(),
            $property->getName()
        ));

        if ($type instanceof \ReflectionIntersectionType) {
            $throw('Property %s->%s has a disallowed intersection type');
        }

        if ($type instanceof \ReflectionUnionType) {
            if (2 !== count($type->getTypes())) {
                $throw('Property %s->%s has more than 2 union types, only Type|null is allowed');
            }

            foreach ($type->getTypes() as $subtype) {
                if ($subtype instanceof \ReflectionIntersectionType) {
                    $throw('Property %s->%s has a disallowed intersection type');
                }

                if (!$subtype->isBuiltin()) {
                    return $subtype;
                }

                if ('null' !== $subtype->getName()) {
                    return $subtype;
                }
            }
        }

        if (null === $type) {
            $throw('Property %s->%s has no valid type detected');
        }

        assert($type instanceof \ReflectionNamedType);

        return $type;
    }
}
