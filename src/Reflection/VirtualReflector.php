<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;

class VirtualReflector
{
    /**
     * @param list<mixed> $attributes
     *
     * @return array<string, Property>
     */
    public function reflect(
        array $attributes
    ): array {
        $fields = [];

        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Field) {
                continue;
            }

            if ($attribute->input instanceof Literal
                || $attribute->input instanceof Dynamic) {
                $fields[$attribute->target] = $attribute->input;
            }

            $fields[$attribute->target] = new Property(
                $attribute->target,
                $attribute->type ?? new Type(
                    'int',
                    null
                ),
                $attribute->bag,
                path: $attribute->input,
                constraints: $attribute->constraints
            );
        }

        return $fields;
    }
}
