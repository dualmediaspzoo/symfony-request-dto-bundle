<?php

namespace DM\DtoRequestBundle\Service\Resolver;

use Doctrine\Common\Annotations\Reader;
use DM\DtoRequestBundle\Annotations\Dto\Bag;
use DM\DtoRequestBundle\Annotations\Dto\Type as TypeAnnotation;
use DM\DtoRequestBundle\Exception\Type\InvalidDateTimeClassException;
use DM\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoTypeExtractorInterface;
use DM\DtoRequestBundle\Model\Type\Dto;
use DM\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

class DtoTypeExtractorHelper implements DtoTypeExtractorInterface
{
    private PropertyInfoExtractorInterface $propertyInfoExtractor;
    private Reader $reader;

    public function __construct(
        PropertyInfoExtractorInterface $propertyInfoExtractor,
        Reader $reader
    ) {
        $this->propertyInfoExtractor = $propertyInfoExtractor;
        $this->reader = $reader;
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
        $root ??= $this->reader->getClassAnnotation($class, Bag::class) ?? new Bag();

        $dto = new Dto();

        foreach ($this->propertyInfoExtractor->getProperties($fqcn) as $property) {
            if (!$this->propertyInfoExtractor->isWritable($fqcn, $property)) { // we won't be able to do anything with this anyway
                continue;
            }

            try {
                $annotations = $this->reader->getPropertyAnnotations(new \ReflectionProperty($fqcn, $property));
            } catch (\ReflectionException $e) { // todo: php8 remove $e param, leave try-catch
                continue;
            }

            $types = $this->propertyInfoExtractor->getTypes($fqcn, $property);

            if (1 !== count($types)) {
                throw new InvalidTypeCountException(sprintf(
                    "Cannot deduct types with multiple specified types for property %s in class %s",
                    $property,
                    $fqcn
                ));
            }

            $propertyClass = $this->getClass($types[0]);

            if (is_subclass_of($propertyClass, DtoInterface::class)) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $model = $this->extract(new \ReflectionClass($propertyClass), $root);
            } else {
                $model = new Property();
            }

            // slightly special handling is required for this
            /** @var FindInterface|null $findAnnotation */
            $findAnnotation = array_values(array_filter($annotations, fn ($o) => $o instanceof FindInterface))[0] ?? null;

            $model->setBag($root) // set default bag
                ->setType($this->getType($types[0]) ?? 'string')
                ->setCollection($types[0]->isCollection())
                ->setParent($dto)
                ->setPropertyAnnotations($annotations)
                ->setFindAnnotation($findAnnotation)
                ->setName($property)
                ->setFqcn($propertyClass)
                ->setDescription($this->propertyInfoExtractor->getShortDescription($fqcn, $property));

            $dto[$property] = $model;

            if (null === $findAnnotation) {
                continue;
            }

            foreach ($findAnnotation->getFields() as $key => $field) {
                if (str_starts_with($field, '$')) { // dynamic
                    continue;
                }

                // simplify usage on the user end
                if (!is_array($constraints = $findAnnotation->getConstraints()[$key] ?? [])) {
                    $constraints = [$constraints];
                }

                $type = $findAnnotation->getTypes()[$key] ?? new TypeAnnotation();

                $subProperty = (new Property())
                    ->setBag($model->getBag())
                    ->setParent($model)
                    ->setName($key)
                    ->setPropertyAnnotations($constraints)
                    ->setType($type->type)
                    ->setSubType($type->subType)
                    ->setCollection($type->collection)
                    ->setFormat($type->format)
                    ->setDescription($findAnnotation->getDescriptions()[$key] ?? null);

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
            (null !== $type->getCollectionValueType() ? $type->getCollectionValueType()->getClassName() : null);
    }

    private function getType(
        Type $type
    ): ?string {
        return !$type->isCollection() ? $type->getBuiltinType() :
            (null !== $type->getCollectionValueType() ? $type->getCollectionValueType()->getBuiltinType() : null);
    }
}
