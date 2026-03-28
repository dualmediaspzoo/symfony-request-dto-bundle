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
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Reflection\Factory\TypeFactory;
use Symfony\Component\Validator\Constraint;

class Reflector
{
    public function __construct(
        private readonly TypeReflector $propertyReflector,
        private readonly VirtualReflector $virtualReflector,
        private readonly PropertyFactory $propertyFactory,
        private readonly TypeFactory $typeFactory
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
            $reflectionType = $this->propertyReflector->reflect($property);
            $name = $property->getName();

            // check if our type is a collection
            $collectionType = match (true) {
                $reflectionType->isBuiltin() && 'array' === $reflectionType->getName() => 'array',
                !$reflectionType->isBuiltin() && is_subclass_of($reflectionType->getName(), Collection::class) => Collection::class,
                default => null,
            };

            $attributes = array_map(
                static fn (\ReflectionAttribute $a) => $a->newInstance(),
                $property->getAttributes()
            );

            // for collections, a Type attribute may override the element type
            $typeAttribute = $collectionType
                ? array_find($attributes, static fn ($m) => $m instanceof TypeAttribute)
                : null;

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

            $typeMetadata = $this->typeFactory->type($reflectionType, $collectionType, $typeAttribute);

            if (!$reflectionType->isBuiltin()
                && is_subclass_of($reflectionType->getName(), AbstractDto::class)) {
                // todo: write only main metadata! (constraints, bag, etc.)
                $results[$name] = new Dto(
                    $name,
                    $typeMetadata,
                    $bag,
                    $path,
                    $constraints
                );

                continue;
            }

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
