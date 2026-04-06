<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use Symfony\Component\TypeInfo\Type;

class VirtualReflector
{
    public function __construct(
        private readonly PropertyFactory $propertyFactory
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

            $constraints = $attribute->constraints;

            if (!is_array($constraints)) {
                $constraints = [$constraints];
            }

            $type = null !== $attribute->fqcn
                ? Type::object($attribute->fqcn)
                : Type::builtin($attribute->type ?? 'int');

            $fields[$attribute->target] = $this->propertyFactory->create(
                $attribute->target,
                $type,
                $attribute->bag,
                $attribute->input,
                $constraints
            );
        }

        return $fields;
    }
}
