<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;

class VirtualReflector
{
    public function __construct(
        private readonly PropertyFactory $propertyFactory,
        private readonly MetaReflector $metaReflector
    ) {
    }

    /**
     * @param list<mixed> $attributes
     *
     * @return array<string, Property|Literal|Dynamic>
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
                continue;
            }

            if (!is_array($constraints = $attribute->constraints)) {
                $constraints = [$constraints];
            }

            if (is_callable($type = $attribute->type)) {
                $type = $type();
            }

            $fields[$attribute->target] = $this->propertyFactory->create(
                $attribute->target,
                $type,
                $attribute->bag,
                $attribute->input,
                $constraints,
                meta: $this->metaReflector->meta($attribute->meta),
                description: $attribute->description
            );
        }

        return $fields;
    }
}
