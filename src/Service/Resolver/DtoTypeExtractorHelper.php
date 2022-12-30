<?php

namespace DualMedia\DtoRequestBundle\Service\Resolver;

use DualMedia\DtoRequestBundle\Attributes\Dto\Bag;
use DualMedia\DtoRequestBundle\Attributes\Dto\Type as TypeAnnotation;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidDateTimeClassException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DualMedia\DtoRequestBundle\Model\Type\Dto;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class DtoTypeExtractorHelper implements DtoTypeExtractorInterface
{
    private PropertyInfoExtractorInterface $propertyInfoExtractor;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfoExtractor
    ) {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
    }

    /**
     * @param \ReflectionClass<DtoInterface> $class
     * @param Bag|null $root
     *
     * @return Dto
     *
     * @throws InvalidTypeCountException
     * @throws InvalidDateTimeClassException
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function extract(
        \ReflectionClass $class,
        ?Bag $root = null
    ): Dto {
        $fqcn = $class->getName();

        if (null === $root) {
            if (!empty($bag = $class->getAttributes(Bag::class))) {
                $bag = $bag[0]->newInstance();
            } else {
                $bag = new Bag();
            }

            $root = $bag;
        }

        $dto = new Dto();

        foreach ($this->propertyInfoExtractor->getProperties($fqcn) ?? [] as $property) {
            if (!$this->propertyInfoExtractor->isWritable($fqcn, $property)) { // we won't be able to do anything with this anyway
                continue;
            }

            try {
                $attributes = array_map(
                    fn (\ReflectionAttribute $a) => $a->newInstance(),
                    (new \ReflectionProperty($fqcn, $property))->getAttributes()
                );
            } catch (\ReflectionException) { // todo: php8 remove $e param, leave try-catch
                continue;
            }

            $types = $this->propertyInfoExtractor->getTypes($fqcn, $property);

            if (1 !== count($types ?? [])) {
                throw new InvalidTypeCountException(sprintf(
                    "Cannot deduct types with multiple specified types for property %s in class %s",
                    $property,
                    $fqcn
                ));
            }

            $propertyClass = $this->getClass($types[0]);

            if (null !== $propertyClass && is_subclass_of($propertyClass, DtoInterface::class)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $model = $this->extract(new \ReflectionClass($propertyClass), $root);
            } else {
                $model = new Property();
            }

            // slightly special handling is required for this
            /** @var FindInterface|null $findAttribute */
            $findAttribute = array_values(array_filter($attributes, fn ($o) => $o instanceof FindInterface))[0] ?? null;

            $model->setBag($root) // set default bag
                ->setType($this->getType($types[0]) ?? 'string')
                ->setCollection($types[0]->isCollection())
                ->setParent($dto)
                ->setPropertyAttributes($attributes)
                ->setFindAttribute($findAttribute)
                ->setName($property)
                ->setFqcn($propertyClass)
                ->setDescription($this->propertyInfoExtractor->getShortDescription($fqcn, $property));

            $dto[$property] = $model;

            if (null === $findAttribute) {
                continue;
            }

            foreach ($findAttribute->getFields() as $key => $field) {
                if (str_starts_with($field, '$')) { // dynamic
                    continue;
                }

                // simplify usage on the user end
                if (!is_array($constraints = $findAttribute->getConstraints()[$key] ?? [])) {
                    $constraints = [$constraints];
                }

                $type = $findAttribute->getTypes()[$key] ?? new TypeAnnotation();

                $subProperty = (new Property())
                    ->setBag($model->getBag())
                    ->setParent($model)
                    ->setName($key)
                    ->setPropertyAttributes($constraints)
                    ->setType($type->type)
                    ->setSubType($type->subType)
                    ->setCollection($type->collection)
                    ->setFormat($type->format)
                    ->setDescription($findAttribute->getDescriptions()[$key] ?? null);

                /**
                 * @psalm-suppress InvalidArgument
                 */
                $model[$key] = $subProperty;
            }
        }

        return $dto;
    }

    private function getClass(
        Type $type
    ): ?string {
        return !$type->isCollection() ?
            $type->getClassName() :
            ($type->getCollectionValueTypes()[0] ?? null)?->getClassName() ?? null;
    }

    private function getType(
        Type $type
    ): ?string {
        return !$type->isCollection() ?
            $type->getBuiltinType() :
            ($type->getCollectionValueTypes()[0] ?? null)?->getBuiltinType() ?? null;
    }
}
