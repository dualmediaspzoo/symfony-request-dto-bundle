<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Type as TypeAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;

class Reflector
{
    public function __construct(
        private readonly PropertyReflector $propertyReflector
    ) {
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return array<string, Property|Dto>
     */
    public function reflect(
        string $class
    ): array {
        $reflection = new \ReflectionClass($class);
        $results = [];

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $type = $this->propertyReflector->reflect($property);
            $name = $property->getName();
            $possibleTypeName = $type->getName();
            $fqcn = null;

            // check if our type is a collection
            $collectionType = match (true) {
                $type->isBuiltin() && 'array' === $possibleTypeName => 'array',
                !$type->isBuiltin() && is_subclass_of($possibleTypeName, Collection::class) => Collection::class,
                default => null,
            };

            $attributes = array_map(
                static fn (\ReflectionAttribute $a) => $a->newInstance(),
                $property->getAttributes()
            );

            // now, if yes we might need to modify the actual type if we have a Type attribute
            if ($collectionType) {
                $typeAttr = null;

                foreach ($attributes as $attribute) {
                    if ($attribute instanceof TypeAttribute) {
                        $typeAttr = $attribute;
                        break;
                    }
                }

                $possibleTypeName = $typeAttr?->type ?? $possibleTypeName;
                $fqcn = $typeAttr?->fqcn;
            }


            if (!$type->isBuiltin()) {
                // check if we need to loop around

                // todo: write only main metadata! (constraints, bag, etc.)
                // todo: differentiate between normal and collections later!
                // this will be an instance of dto
                if (is_subclass_of($possibleTypeName, AbstractDto::class)) {
                    $results[$name] = new Dto(
                        $name,
                        new Type(
                            'object',
                            $collectionType,
                            $possibleTypeName
                        )
                    );

                    continue;
                }
            }

            $results[$name] = new Property(
                $name,
                new Type(
                    $possibleTypeName,
                    $collectionType,
                    $fqcn
                )
            );
        }

        return $results;
    }
}
