<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy as FindByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy as FindOneByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format as FormatAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Limit as LimitAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Offset as OffsetAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;

class MetaReflector
{
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
                $attribute instanceof FormatAttribute => new Format($attribute->format),
                $attribute instanceof LimitAttribute => new Limit($attribute->count),
                $attribute instanceof OffsetAttribute => new Offset($attribute->count),
                default => null,
            };

            if (null !== $item) {
                $meta[] = $item;
            }
        }

        return $meta;
    }
}
