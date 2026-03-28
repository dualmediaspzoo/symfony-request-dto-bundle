<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag as BagAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path as PathAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Type as TypeAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;
use Symfony\Component\Validator\Constraint;

class Reflector
{
    public function __construct(
        private readonly TypeReflector $propertyReflector,
        private readonly VirtualReflector $virtualReflector,
        private readonly PropertyFactory $propertyFactory
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
                $typeAttr = array_find(
                    $attributes,
                    static fn ($m) => $m instanceof TypeAttribute
                );

                $possibleTypeName = $typeAttr->type ?? $possibleTypeName;
                $fqcn = $typeAttr?->fqcn;
            }

            $bag = array_find(
                $attributes,
                static fn ($m) => $m instanceof BagAttribute
            )?->bag;

            $path = array_find(
                $attributes,
                static fn ($m) => $m instanceof PathAttribute
            )?->path;

            $constraints = [];

            foreach ($attributes as $attribute) {
                if ($attribute instanceof Constraint) {
                    $constraints[] = $attribute;
                }
            }

            if (!$type->isBuiltin()
                && is_subclass_of($possibleTypeName, AbstractDto::class)) {
                // check if we need to loop around
                // todo: write only main metadata! (constraints, bag, etc.)

                // this will be an instance of dto
                $results[$name] = new Dto(
                    $name,
                    new Type(
                        'object',
                        $collectionType,
                        $possibleTypeName
                    ),
                    $bag,
                    $path,
                    $constraints
                );

                continue;
            }

            if (null === $fqcn && !$type->isBuiltin()) {
                $fqcn = $possibleTypeName;
                $possibleTypeName = 'object';
            }

            $typeMetadata = new Type(
                $possibleTypeName,
                $collectionType,
                $fqcn
            );

            $results[$name] = $this->propertyFactory->create(
                $name,
                $typeMetadata,
                $bag,
                $path,
                $constraints,
                $this->virtualReflector->reflect($attributes)
            );
        }

        return $results;
    }
}
