<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Doctrine\Common\Collections\Collection;

class MetaReflector
{
    public function collection(
        \ReflectionNamedType $type
    ): string|null {
        return match (true) {
            $type->isBuiltin() && 'array' === $type->getName() => 'array',
            !$type->isBuiltin() && is_subclass_of($type->getName(), Collection::class) => Collection::class,
            default => null,
        };
    }
}
