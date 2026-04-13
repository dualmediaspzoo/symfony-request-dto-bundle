<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\AsDoctrineReference as AsDoctrineReferenceAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy as FindByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy as FindOneByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format as FormatAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey as FromKeyAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Limit as LimitAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Offset as OffsetAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\OrderBy as OrderByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithAllowedEnum;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithLabelProcessor as WithLabelProcessorAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\AllowedEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\LabelProcessor;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;
use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;

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
                $attribute instanceof FromKeyAttribute => new FromKey(),
                $attribute instanceof LimitAttribute => new Limit($attribute->count),
                $attribute instanceof OffsetAttribute => new Offset($attribute->count),
                $attribute instanceof AsDoctrineReferenceAttribute => new AsDoctrineReference(),
                $attribute instanceof OrderByAttribute => new OrderBy($attribute->field, $attribute->order->value),
                $attribute instanceof WithLabelProcessorAttribute => new LabelProcessor($attribute->serviceId),
                $attribute instanceof WithAllowedEnum => new AllowedEnum((array)$attribute->allowed), // @phpstan-ignore-line
                default => null,
            };

            if (null !== $item) {
                $meta[] = $item;
            }
        }

        return $meta;
    }
}
