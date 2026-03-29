<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy as FindByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy as FindOneByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Limit as LimitAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Offset as OffsetAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Type as TypeAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;

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

    /**
     * @param list<object> $attributes
     */
    public function type(
        array $attributes
    ): TypeAttribute|null {
        return array_find($attributes, static fn ($m) => $m instanceof TypeAttribute);
    }

    /**
     * @param list<object> $attributes
     *
     * @return list<object>
     */
    public function meta(
        array $attributes
    ): array {
        $meta = [];

        foreach ($attributes as $attribute) {
            $item = match (true) {
                $attribute instanceof FindByAttribute => new FindBy(!$attribute instanceof FindOneByAttribute),
                $attribute instanceof LimitAttribute => new Limit($attribute->count),
                $attribute instanceof OffsetAttribute => new Offset($attribute->count),
                default => null,
            };
        }

        return $meta;
    }
}
