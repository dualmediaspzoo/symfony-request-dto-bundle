<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag as BagAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path as PathAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;
use Symfony\Component\Validator\Constraint;

class Reflector
{
    public function __construct(
        private readonly VirtualReflector $virtualReflector,
        private readonly PropertyFactory $propertyFactory,
        private readonly MetaReflector $metaReflector,
        private readonly TypeResolver $typeResolver
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
            $type = $this->typeResolver->resolve($property);
            $name = $property->getName();

            $attributes = array_map(
                static fn (\ReflectionAttribute $a) => $a->newInstance(),
                $property->getAttributes()
            );

            $bag = array_find($attributes, static fn ($m) => $m instanceof BagAttribute)?->bag;
            $path = array_find($attributes, static fn ($m) => $m instanceof PathAttribute)?->path;
            $constraints = array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint));

            $className = TypeInfoUtils::getClassName($type)
                ?? TypeInfoUtils::getCollectionValueClassName($type);

            if (null !== $className && is_subclass_of($className, AbstractDto::class)) {
                $results[$name] = new Dto(
                    $name,
                    $type,
                    $bag,
                    $path,
                    $constraints
                );

                continue;
            }

            $results[$name] = $this->propertyFactory->create(
                $name,
                $type,
                $bag,
                $path,
                $constraints,
                $this->virtualReflector->reflect($attributes),
                $this->metaReflector->meta($attributes)
            );
        }

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
