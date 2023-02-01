<?php

namespace DualMedia\DtoRequestBundle\Model\Type;

use DualMedia\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Attributes\Dto\Bag;
use DualMedia\DtoRequestBundle\Attributes\Dto\Format;
use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidDateTimeClassException;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\FindInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\PathInterface;
use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyAccess\Exception\OutOfBoundsException;
use Symfony\Component\PropertyAccess\PropertyPath;
use Symfony\Component\PropertyAccess\PropertyPathBuilder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Property model for dto type reading
 *
 * @implements \ArrayAccess<string, Property>
 * @implements \IteratorAggregate<string, Property>
 */
class Property implements \ArrayAccess, \IteratorAggregate
{
    protected string $name;
    protected Bag $bag;
    protected ?string $path = null;

    /**
     * @var array<class-string<DtoAttributeInterface>, list<DtoAttributeInterface>>
     */
    protected array $dtoAttributes = [];

    /**
     * @var list<Constraint>
     */
    protected array $constraints = [];
    protected ?FindInterface $findAttribute = null;
    protected ?Property $parent = null;
    protected ?string $fqcn = null;
    protected string $type;
    protected ?string $subType = null;
    protected bool $collection = false;
    protected ?Format $format = null;
    protected ?string $description = null;

    /**
     * @var array<string, Property>
     */
    protected array $properties = [];
    protected ?HttpActionInterface $httpAction = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(
        string $name
    ): static {
        $this->name = $name;

        return $this;
    }

    public function getFqcn(): ?string
    {
        return $this->fqcn;
    }

    /**
     * @throws InvalidDateTimeClassException
     */
    public function setFqcn(
        ?string $fqcn
    ): static {
        $this->fqcn = $fqcn; // temporarily set this so checks can be non-repeating
        $this->fqcn = $this->validateClassFqcn($fqcn);

        return $this;
    }

    public function isCollection(): bool
    {
        return $this->collection;
    }

    public function setCollection(
        bool $collection
    ): static {
        $this->collection = $collection;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @psalm-suppress NullableReturnStatement
     * @psalm-suppress InvalidNullableReturnType
     */
    public function getOAType(): string
    {
        if ($this->isEnum()) {
            if ($this->hasDtoAttribute(FromKey::class)) {
                return 'string'; // keys are text only
            }

            return 'int' === $this->getEnumType() ? 'integer' : 'string';
        } elseif ($this->isDate()) {
            return 'string';
        }

        return $this->fixOAType($this->getSubType()) ?? $this->fixOAType($this->getType()) ?? 'string';
    }

    public function setType(
        string $type
    ): static {
        $this->type = $type;

        return $this;
    }

    public function getSubType(): ?string
    {
        return $this->subType;
    }

    public function setSubType(
        ?string $subType
    ): Property {
        $this->subType = $subType;

        return $this;
    }

    public function getBag(): Bag
    {
        return $this->bag;
    }

    public function setBag(
        Bag $bag
    ): static {
        $this->bag = $bag;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(
        ?string $path
    ): static {
        $this->path = $path;

        return $this;
    }

    public function getRealPath(): string
    {
        return $this->getPath() ?? $this->getName();
    }

    public function addDtoAttribute(
        DtoAttributeInterface $attribute
    ): static {
        if ($attribute instanceof PathInterface) {
            $this->setPath($attribute->getPath() ?? $this->getPath());
        }

        if ($attribute instanceof Bag) {
            $this->setBag($attribute);
        } elseif ($attribute instanceof Format) {
            $this->setFormat($attribute);
            $this->setSubType('string');
        } elseif ($attribute instanceof HttpActionInterface) {
            $this->httpAction = $attribute;
        } else {
            if (!array_key_exists($class = get_class($attribute), $this->dtoAttributes)) {
                $this->dtoAttributes[$class] = [];
            }

            $this->dtoAttributes[$class][] = $attribute;
        }

        return $this;
    }

    /**
     * @template T of DtoAttributeInterface
     *
     * @param class-string<T> $class
     *
     * @return list<T>
     *
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     */
    public function getDtoAttributes(
        string $class
    ): array {
        // @phpstan-ignore-next-line
        return $this->dtoAttributes[$class] ?? [];
    }

    public function getHttpAction(): ?HttpActionInterface
    {
        return $this->httpAction;
    }

    /**
     * @param class-string<DtoAttributeInterface> $class
     *
     * @return bool
     */
    public function hasDtoAttribute(
        string $class
    ): bool {
        return !empty($this->getDtoAttributes($class));
    }

    public function getFindAttribute(): ?FindInterface
    {
        return $this->findAttribute;
    }

    public function setFindAttribute(
        ?FindInterface $findAttribute
    ): static {
        $this->findAttribute = $findAttribute;

        return $this;
    }

    public function getParent(): ?Property
    {
        return $this->parent;
    }

    public function setParent(
        ?Property $parent
    ): Property {
        $this->parent = $parent;

        return $this;
    }

    public function getFormat(): ?Format
    {
        return $this->format;
    }

    public function setFormat(
        ?Format $format
    ): static {
        $this->format = $format;

        if (null !== $format) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->setFqcn(\DateTimeImmutable::class);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(
        ?string $description
    ): static {
        $this->description = $description;

        return $this;
    }

    public function isEnum(): bool
    {
        return null !== $this->getFqcn() &&
            is_subclass_of($this->getFqcn(), \BackedEnum::class);
    }

    public function getEnumType(): ?string
    {
        if (!$this->isEnum()) {
            return null;
        }

        return (string)(new \ReflectionEnum($this->getFqcn()))->getBackingType(); // @phpstan-ignore-line
    }

    public function isDate(): bool
    {
        return null !== $this->getFqcn() &&
            is_subclass_of($this->getFqcn(), \DateTimeInterface::class);
    }

    /**
     * These choices are mapped respectively as their cases
     *
     * @return list<\BackedEnum>
     */
    public function getEnumCases(): array
    {
        if (!$this->isEnum()) {
            return [];
        }

        /**
         * @var list<\BackedEnum> $enums
         * @phpstan-ignore-next-line
         */
        $enums = call_user_func([$this->getFqcn(), 'cases']);

        /**
         * @phpstan-ignore-next-line
         * @var AllowEnum $allowed
         * @psalm-suppress NoInterfaceProperties
         */
        if (null !== ($allowed = $this->getDtoAttributes(AllowEnum::class)[0] ?? null) &&
            !empty($allowed->allowed)) {
            $enums = $allowed->allowed;
        }

        return $enums;
    }

    /**
     * @return list<Constraint>
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    public function isRequired(): bool
    {
        if (null !== $this->findConstraint(Assert\NotNull::class)) {
            return true;
        }

        if (null === ($constraint = $this->findConstraint(Assert\NotBlank::class))) {
            return false;
        }

        return !$constraint->allowNull;
    }

    public function applyCollectionConstraints(
        Schema $schema
    ): void {
        if (null !== ($count = $this->findConstraint(Assert\Count::class))) {
            if (isset($count->min)) {
                $schema->minItems = (int)$count->min;
            }

            if (isset($count->max)) {
                $schema->maxItems = (int)$count->max;
            }
        }
    }

    /**
     * @param mixed[] $attributes
     *
     * @return $this
     */
    public function setPropertyAttributes(
        array $attributes
    ): static {
        foreach (array_filter($attributes, fn ($o) => $o instanceof DtoAttributeInterface && !($o instanceof FindInterface)) as $item) {
            $this->addDtoAttribute($item);
        }

        foreach (array_filter($attributes, fn ($o) => $o instanceof Constraint) as $item) {
            $this->constraints[] = $item;
        }

        return $this;
    }

    /**
     * The builder specified here must always adhere to the model schema
     *
     * @phpstan-ignore-next-line
     * @param PropertyPath $propertyPath
     * @param PropertyPathBuilder|null $builder
     * @param int $index
     *
     * @return string
     */
    public function fixPropertyPath(
        PropertyPath $propertyPath,
        ?PropertyPathBuilder $builder = null,
        int $index = 0
    ): string {
        $builder ??= new PropertyPathBuilder($propertyPath);
        $jump = $this->isCollection() ? 2 : 1; // array index counts as a position in path

        try {
            $next = $propertyPath->getElement($jump);
        } catch (OutOfBoundsException) {
            $next = null;
        }

        if (null !== ($find = $this->getFindAttribute())) {
            $builder->replaceByProperty(
                $index,
                $find->getErrorPath() ?? $find->getFirstNonDynamicField() ?? $this->getName()
            );
        } elseif (null !== $this->getPath()) {
            $builder->replaceByProperty($index, $this->getPath());
        }
        $index += $jump;

        // we want to exit if there's no next jump, we're hitting the index or the next element does not exist (custom path)
        if (null === $next || $index === $builder->getLength() || !isset($this[$next])) {
            return (string)$builder;
        }

        // we need to jump to the next property, which is always here as the actual prop name
        return $this[$next]->fixPropertyPath($propertyPath, $builder, $index);
    }

    /**
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists(
        $offset
    ): bool {
        return isset($this->properties[$offset]);
    }

    /**
     * @param string $offset
     *
     * @return Property|null
     */
    public function offsetGet(
        $offset
    ): ?Property {
        return $this->properties[$offset] ?? null;
    }

    /**
     * @param string $offset
     * @param Property $value
     */
    public function offsetSet(
        $offset,
        $value
    ): void {
        $this->properties[$offset] = $value;
    }

    /**
     * @param string $offset
     */
    public function offsetUnset(
        $offset
    ): void {
        unset($this->properties[$offset]);
    }

    /**
     * @return \Traversable<string, Property>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->properties;
    }

    /**
     * @template T of Constraint
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    public function findConstraint(
        string $class,
        bool $firstAll = false
    ): ?Constraint {
        $all = null;

        foreach ($this->constraints as $constraint) {
            if (!($constraint instanceof Assert\All)) {
                continue;
            }

            if (null !== ($all = $this->findFromArray($constraint->constraints, $class))) {
                break;
            }
        }

        $any = $this->findFromArray($this->constraints, $class);

        if ($firstAll) {
            return $all ?? $any;
        }

        return $any ?? $all;
    }

    /**
     * @throws InvalidDateTimeClassException
     */
    private function validateClassFqcn(
        ?string $class
    ): ?string {
        if (null === $class ||
            (!is_subclass_of($class, \DateTimeInterface::class) &&
            !is_subclass_of($class, \BackedEnum::class))) {
            return $class;
        }

        if (is_subclass_of($class, \DateTimeInterface::class)) {
            if (is_subclass_of($class, \DateTime::class)) {
                throw new InvalidDateTimeClassException(sprintf(
                    'Only %s and %s classes are supported for DateTime',
                    \DateTimeInterface::class,
                    \DateTimeImmutable::class
                ));
            }

            $this->setSubType('string');
        } else {
            if ($this->hasDtoAttribute(FromKey::class)) {
                $this->setSubType('string'); // keys are text only
            } else {
                $this->setSubType($this->getEnumType());
            }
        }

        return $class;
    }

    /**
     * @template T of Constraint
     *
     * @param array<array-key, Constraint> $constraints
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function findFromArray(
        array $constraints,
        string $class
    ): ?Constraint {
        foreach ($constraints as $constraint) {
            if (is_a($constraint, $class)) {
                return $constraint;
            }
        }

        return null;
    }

    private function fixOAType(
        ?string $type
    ): ?string {
        if (null === $type) {
            return null;
        }

        if ('bool' === $this->getType()) {
            return 'boolean';
        } elseif ('int' === $this->getType()) {
            return 'integer';
        } elseif ('float' === $this->getType()) {
            return 'number';
        }

        return $type;
    }
}
