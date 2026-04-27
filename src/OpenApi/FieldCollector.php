<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use DualMedia\DtoRequestBundle\Metadata\Model\AllowedEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\LabelProcessor;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedDto;
use DualMedia\DtoRequestBundle\OpenApi\Model\DescribedField;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use DualMedia\DtoRequestBundle\Resolve\Interface\LabelProcessorInterface;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FieldCollector
{
    /**
     * @param ServiceLocator<LabelProcessorInterface> $labelProcessors
     */
    public function __construct(
        private readonly MainDtoMemoizerInterface $memoizer,
        private readonly ServiceLocator $labelProcessors
    ) {
    }

    /**
     * @param class-string<AbstractDto> $class
     */
    public function collect(
        string $class,
        BagEnum $defaultBag = BagEnum::Request
    ): DescribedDto|null {
        if (null === ($mainDto = $this->memoizer->get($class))) {
            return null;
        }

        $effectiveBag = $mainDto->defaultBag ?? $defaultBag;
        $fields = $this->walkFields($mainDto->fields, $effectiveBag);
        $actions = [];
        $this->collectActions($mainDto->fields, $actions, [$class => true]);

        return new DescribedDto(
            class: $class,
            fields: $fields,
            meta: $mainDto->meta,
            actions: $actions,
        );
    }

    /**
     * @param array<string, Property|Dto> $fields
     * @param list<Action> $out
     * @param array<class-string, true> $visited
     */
    private function collectActions(
        array $fields,
        array &$out,
        array $visited
    ): void {
        foreach ($fields as $meta) {
            if ($meta instanceof Dto) {
                /** @var class-string<AbstractDto>|null $innerClass */
                $innerClass = TypeInfoUtils::getClassName($meta->type)
                    ?? TypeInfoUtils::getCollectionValueClassName($meta->type);

                if (null === $innerClass
                    || !is_subclass_of($innerClass, AbstractDto::class)
                    || isset($visited[$innerClass])
                    || null === ($nested = $this->memoizer->get($innerClass))
                ) {
                    continue;
                }

                $this->collectActions($nested->fields, $out, [...$visited, $innerClass => true]);
            }

            foreach ($meta->meta as $item) {
                if ($item instanceof Action) {
                    $out[] = $item;
                }
            }
        }
    }

    /**
     * @param array<string, Property|Dto> $fields
     *
     * @return list<DescribedField>
     */
    private function walkFields(
        array $fields,
        BagEnum $defaultBag
    ): array {
        $out = [];

        foreach ($fields as $name => $meta) {
            if ($meta instanceof Dto) {
                $out[] = $this->describeDto($meta, $defaultBag);

                continue;
            }

            if (MetadataUtils::exists(\DualMedia\DtoRequestBundle\Metadata\Model\FindBy::class, $meta->meta)) {
                foreach ($this->describeVirtuals($meta, $defaultBag) as $virtualField) {
                    $out[] = $virtualField;
                }

                continue;
            }

            $out[] = $this->describeProperty($meta, $defaultBag);
        }

        return $out;
    }

    private function describeProperty(
        Property $property,
        BagEnum $defaultBag
    ): DescribedField {
        $type = $property->type;
        $isCollection = TypeInfoUtils::isCollection($type);
        $oaType = TypeMapper::toOpenApi($type);

        // FromKey switches the value space from the backing type to case names,
        // so the schema must report `string` regardless of whether the enum is
        // int-backed or non-backed.
        $innerForEnumCheck = $isCollection
            ? (TypeInfoUtils::getCollectionValueType($type) ?? $type)
            : $type;

        if (MetadataUtils::exists(FromKey::class, $property->meta)
            && TypeMapper::isEnum($innerForEnumCheck)) {
            $oaType = 'string';
        }

        $constraints = $this->filterDefaultGroupConstraints($property->constraints);

        return new DescribedField(
            name: $property->name,
            path: $property->getRealPath(),
            bag: $this->pickBag($property->bag, $type, $defaultBag),
            oaType: $oaType,
            isCollection: $isCollection,
            required: $this->isRequired($constraints),
            nullable: !$this->isRequired($constraints),
            constraints: $constraints,
            children: [],
            enumCases: $this->resolveEnumCases($property),
            meta: $property->meta,
            description: $property->description,
        );
    }

    private function describeDto(
        Dto $dto,
        BagEnum $defaultBag
    ): DescribedField {
        $isCollection = TypeInfoUtils::isCollection($dto->type);

        /** @var class-string<AbstractDto>|null $innerClass */
        $innerClass = $isCollection
            ? TypeInfoUtils::getCollectionValueClassName($dto->type)
            : TypeInfoUtils::getClassName($dto->type);

        $children = [];

        if (null !== $innerClass && is_subclass_of($innerClass, AbstractDto::class)) {
            $nested = $this->memoizer->get($innerClass);

            if (null !== $nested) {
                $children = $this->walkFields($nested->fields, $nested->defaultBag ?? $dto->bag ?? $defaultBag);
            }
        }

        $constraints = $this->filterDefaultGroupConstraints($dto->constraints);

        return new DescribedField(
            name: $dto->name,
            path: $dto->getRealPath(),
            bag: $dto->bag ?? $defaultBag,
            oaType: 'object',
            isCollection: $isCollection,
            required: $this->isRequired($constraints),
            nullable: !$this->isRequired($constraints),
            constraints: $constraints,
            children: $children,
            enumCases: [],
            meta: $dto->meta,
            description: $dto->description,
        );
    }

    /**
     * @return list<DescribedField>
     */
    private function describeVirtuals(
        Property $property,
        BagEnum $defaultBag
    ): array {
        $out = [];
        // A `#[Bag]` on the FindBy parent property scopes its virtual fields
        // (e.g. routing them to path parameters); inherit it as the per-virtual
        // default so an unqualified virtual doesn't fall back to the DTO bag.
        $virtualDefaultBag = $property->bag ?? $defaultBag;

        foreach ($property->virtual as $virtual) {
            if (!$virtual instanceof Property) {
                continue;
            }

            $out[] = $this->describeProperty($virtual, $virtualDefaultBag);
        }

        return $out;
    }

    private function pickBag(
        BagEnum|null $bag,
        Type $type,
        BagEnum $default
    ): BagEnum {
        if (null !== $bag) {
            return $bag;
        }

        if (TypeMapper::isUploadedFile($type)) {
            return BagEnum::Files;
        }

        return $default;
    }

    /**
     * Keep only constraints whose validation groups apply to the documented
     * "default" payload — i.e. either no groups configured or one of them is
     * the standard `Default` group. Symfony defaults a Constraint's groups to
     * `['Default']` at construction unless the user passed something explicit,
     * so a constraint with `groups: ['admin']` (and no `Default`) is excluded.
     *
     * @param list<Constraint> $constraints
     *
     * @return list<Constraint>
     */
    private function filterDefaultGroupConstraints(
        array $constraints
    ): array {
        $out = [];

        foreach ($constraints as $constraint) {
            $groups = $constraint->groups ?? [];

            if ([] === $groups || in_array(Constraint::DEFAULT_GROUP, $groups, true)) {
                $out[] = $constraint;
            }
        }

        return $out;
    }

    /**
     * @param list<Constraint> $constraints
     */
    private function isRequired(
        array $constraints
    ): bool {
        foreach ($constraints as $constraint) {
            $result = match (true) {
                $constraint instanceof NotNull => true,
                $constraint instanceof NotBlank => !$constraint->allowNull,
                $constraint instanceof Count => null !== $constraint->min && $constraint->min >= 1,
                default => null,
            };

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string|int>
     */
    private function resolveEnumCases(
        Property $property
    ): array {
        if (null === ($enumClass = TypeMapper::backedEnumClass($property->type))) {
            return [];
        }

        $allowed = MetadataUtils::single(AllowedEnum::class, $property->meta);

        /** @var list<\BackedEnum> $cases */
        $cases = null !== $allowed ? array_values(array_filter(
            $allowed->allowed,
            static fn ($e) => $e instanceof \BackedEnum
        )) : $enumClass::cases();

        if (!MetadataUtils::exists(FromKey::class, $property->meta)) {
            return array_map(static fn (\BackedEnum $e) => $e->value, $cases);
        }

        if (null === ($processor = $this->resolveLabelProcessor($property))) {
            return array_map(static fn (\BackedEnum $e): string => $e->name, $cases);
        }

        return array_map(static fn (\BackedEnum $e): string => $processor->normalize($e->name), $cases);
    }

    private function resolveLabelProcessor(
        Property $property
    ): LabelProcessorInterface|null {
        $lp = MetadataUtils::single(LabelProcessor::class, $property->meta);

        if (null === $lp || !$this->labelProcessors->has($lp->serviceId)) {
            return null;
        }

        return $this->labelProcessors->get($lp->serviceId);
    }
}
