<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag as BagAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path as PathAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Reflection\Factory\TypeFactory;
use Symfony\Component\Validator\Constraint;

class Reflector
{
    public function __construct(
        private readonly TypeReflector $propertyReflector,
        private readonly VirtualReflector $virtualReflector,
        private readonly PropertyFactory $propertyFactory,
        private readonly TypeFactory $typeFactory,
        private readonly MetaReflector $metaReflector
    ) {
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    public function reflect(
        string $class
    ): MainDto {
        $reflection = new \ReflectionClass($class);
        $results = [];

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $reflectionType = $this->propertyReflector->reflect($property);
            $name = $property->getName();

            // prepare all attributes
            $attributes = array_map(
                static fn (\ReflectionAttribute $a) => $a->newInstance(),
                $property->getAttributes()
            );

            // check if our type is a collection
            $collectionType = $this->metaReflector->collection($reflectionType);

            // for collections, a Type attribute may override the element type
            $typeAttribute = null === $collectionType
                ? null
                : $this->metaReflector->type($attributes);

            $bag = array_find($attributes, static fn ($m) => $m instanceof BagAttribute)?->bag;
            $path = array_find($attributes, static fn ($m) => $m instanceof PathAttribute)?->path;

            $constraints = array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint));
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
                $this->virtualReflector->reflect($attributes),
                $this->metaReflector->meta($attributes)
            );
        }

        // main dto constraints
        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $reflection->getAttributes()
        );

        return new MainDto(
            $results,
            array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint))
        );
    }
}
