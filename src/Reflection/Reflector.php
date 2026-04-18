<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag as BagAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path as PathAttribute;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\ValidateWithGroups;
use DualMedia\DtoRequestBundle\Metadata\Model\WithObjectProvider;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;
use Symfony\Component\Validator\Constraint;

class Reflector
{
    /**
     * @param ServiceLocator<GroupProviderInterface> $groupProviderLocator
     * @param ServiceLocator<ProviderInterface<object>> $objectProviderLocator
     */
    public function __construct(
        private readonly VirtualReflector $virtualReflector,
        private readonly PropertyFactory $propertyFactory,
        private readonly MetaReflector $metaReflector,
        private readonly TypeResolver $typeResolver,
        private readonly ServiceLocator $groupProviderLocator,
        private readonly ServiceLocator $objectProviderLocator
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

            $description = ReflectionUtils::extractShortDescription($property->getDocComment());

            if (null !== $className && is_subclass_of($className, AbstractDto::class)) {
                $results[$name] = new Dto(
                    name: $name,
                    type: $type,
                    bag: $bag,
                    path: $path,
                    constraints: $constraints,
                    description: $description
                );

                continue;
            }

            $propertyMeta = $this->metaReflector->meta($attributes);

            $results[$name] = $this->propertyFactory->create(
                $name,
                $type,
                $bag,
                $path,
                $constraints,
                $this->virtualReflector->reflect($attributes),
                $propertyMeta,
                $this->resolveObjectProviderServiceId($class, $name, $propertyMeta),
                $description
            );
        }

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $reflection->getAttributes()
        );

        $meta = $this->metaReflector->meta($attributes);
        $classBag = array_find($attributes, static fn ($m) => $m instanceof BagAttribute)?->bag;

        return new MainDto(
            fields: $results,
            constraints: array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint)),
            meta: $meta,
            validationGroupsServiceId: $this->resolveValidationGroupsServiceId($class, $meta),
            defaultBag: $classBag,
        );
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return list<object>
     */
    public function reflectClassMeta(
        string $class
    ): array {
        $reflection = new \ReflectionClass($class);

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $reflection->getAttributes()
        );

        return $this->metaReflector->meta($attributes);
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return list<Constraint>
     */
    public function reflectClassConstraints(
        string $class
    ): array {
        $reflection = new \ReflectionClass($class);

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $reflection->getAttributes()
        );

        return array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint));
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return list<Constraint>
     */
    public function reflectPropertyConstraints(
        string $class,
        string $propertyName
    ): array {
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($propertyName);

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $property->getAttributes()
        );

        return array_values(array_filter($attributes, static fn ($o) => $o instanceof Constraint));
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return list<Constraint>
     */
    public function reflectVirtualConstraints(
        string $class,
        string $propertyName,
        string $targetName
    ): array {
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($propertyName);

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $property->getAttributes()
        );

        foreach ($attributes as $attribute) {
            if (!$attribute instanceof Field) {
                continue;
            }

            if ($attribute->input instanceof Literal
                || $attribute->input instanceof Dynamic) {
                continue;
            }

            if ($attribute->target !== $targetName) {
                continue;
            }

            $constraints = $attribute->constraints;

            if (!is_array($constraints)) {
                $constraints = [$constraints];
            }

            return $constraints;
        }

        return [];
    }

    /**
     * @param class-string<AbstractDto> $class
     *
     * @return list<object>
     */
    public function reflectPropertyMeta(
        string $class,
        string $propertyName
    ): array {
        $reflection = new \ReflectionClass($class);
        $property = $reflection->getProperty($propertyName);

        $attributes = array_map(
            static fn (\ReflectionAttribute $a) => $a->newInstance(),
            $property->getAttributes()
        );

        return $this->metaReflector->meta($attributes);
    }

    /**
     * @param class-string<AbstractDto> $class
     * @param list<object> $meta
     */
    private function resolveObjectProviderServiceId(
        string $class,
        string $propertyName,
        array $meta
    ): string|null {
        $wop = MetadataUtils::single(WithObjectProvider::class, $meta);

        if (null === $wop) {
            return null;
        }

        return ReflectionUtils::resolveAndValidateServiceId(
            $wop->closure,
            $this->objectProviderLocator,
            '#[WithObjectProvider]',
            sprintf('%s::%s', $class, $propertyName),
            'an object provider'
        );
    }

    /**
     * @param class-string<AbstractDto> $class
     * @param list<object> $meta
     */
    private function resolveValidationGroupsServiceId(
        string $class,
        array $meta
    ): string|null {
        $vwg = MetadataUtils::single(ValidateWithGroups::class, $meta);

        if (null === $vwg) {
            return null;
        }

        return ReflectionUtils::resolveAndValidateServiceId(
            $vwg->closure,
            $this->groupProviderLocator,
            '#[ValidateWithGroups]',
            $class,
            'a group provider'
        );
    }
}
