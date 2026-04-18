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
        $mainDto = $this->memoizer->get($class);

        if (null === $mainDto) {
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

                if (null !== $innerClass
                    && is_subclass_of($innerClass, AbstractDto::class)
                    && !isset($visited[$innerClass])
                ) {
                    $nested = $this->memoizer->get($innerClass);

                    if (null !== $nested) {
                        $this->collectActions($nested->fields, $out, [...$visited, $innerClass => true]);
                    }
                }

                continue;
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

        return new DescribedField(
            name: $property->name,
            path: $property->getRealPath(),
            bag: $this->pickBag($property->bag, $type, $defaultBag),
            oaType: $oaType,
            isCollection: $isCollection,
            required: $this->isRequired($property->constraints),
            nullable: !$this->isRequired($property->constraints),
            constraints: $property->constraints,
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

        return new DescribedField(
            name: $dto->name,
            path: $dto->getRealPath(),
            bag: $dto->bag ?? $defaultBag,
            oaType: 'object',
            isCollection: $isCollection,
            required: $this->isRequired($dto->constraints),
            nullable: !$this->isRequired($dto->constraints),
            constraints: $dto->constraints,
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

        foreach ($property->virtual as $virtual) {
            if (!$virtual instanceof Property) {
                continue;
            }

            $out[] = $this->describeProperty($virtual, $defaultBag);
        }

        return $out;
    }

    private function pickBag(
        BagEnum|null $bag,
        \Symfony\Component\TypeInfo\Type $type,
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
     * @param list<\Symfony\Component\Validator\Constraint> $constraints
     */
    private function isRequired(
        array $constraints
    ): bool {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof NotNull || $constraint instanceof NotBlank) {
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
        $enumClass = TypeMapper::backedEnumClass($property->type);

        if (null === $enumClass) {
            return [];
        }

        $allowed = MetadataUtils::single(AllowedEnum::class, $property->meta);

        /** @var list<\BackedEnum> $cases */
        $cases = null !== $allowed ? array_values(array_filter(
            $allowed->allowed,
            static fn ($e) => $e instanceof \BackedEnum
        )) : $enumClass::cases();

        $fromKey = MetadataUtils::single(FromKey::class, $property->meta);

        if (null === $fromKey) {
            return array_map(static fn (\BackedEnum $e) => $e->value, $cases);
        }

        $processor = $this->resolveLabelProcessor($property);

        if (null === $processor) {
            return array_map(static fn (\BackedEnum $e) => $e->name, $cases);
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
