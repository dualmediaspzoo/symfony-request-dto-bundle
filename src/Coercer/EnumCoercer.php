<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\AllowedEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\LabelProcessor;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Resolve\Interface\LabelProcessorInterface;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isSatisfiedBy(static fn (TypeInfo $t): bool => $t instanceof EnumType);
})]
class EnumCoercer implements CoercerInterface
{
    /**
     * @param ServiceLocator<LabelProcessorInterface> $labelProcessorLocator
     */
    public function __construct(
        private readonly StringCoercer $stringCoercer,
        private readonly IntegerCoercer $integerCoercer,
        private readonly ServiceLocator $labelProcessorLocator
    ) {
    }

    #[\Override]
    public function coerce(
        Property $property,
        Constraint|array $constraints = []
    ): Result {
        /** @var class-string<\UnitEnum> $enumClass */
        $enumClass = TypeInfoUtils::getClassName($property->type)
            ?? TypeInfoUtils::getCollectionValueClassName($property->type);

        $allowed = MetadataUtils::single(AllowedEnum::class, $property->meta)->allowed ?? $enumClass::cases();

        if (MetadataUtils::exists(FromKey::class, $property->meta)) {
            $labelProcessorModel = MetadataUtils::single(LabelProcessor::class, $property->meta);

            $normalize = static fn (string $v) => $v;

            if (null !== $labelProcessorModel) {
                $processor = $this->labelProcessorLocator->get($labelProcessorModel->serviceId);

                $normalize = $processor->normalize(...);
            }

            /** @var array<string, \UnitEnum> $existing */
            $existing = [];

            foreach ($allowed as $case) {
                $existing[$normalize($case->name)] = $case;
            }

            return CoercionUtils::coerce(
                $property,
                static function (mixed $val) use ($existing): mixed {
                    if (!is_string($val)) {
                        return $val;
                    }

                    return $existing[$val] ?? $val;
                },
                new Type(type: $enumClass),
                $this->stringCoercer->coerce($property, new Choice(choices: array_keys($existing))),
                additionalConstraints: $constraints
            );
        }

        $backingTypeId = null;
        $checkType = TypeInfoUtils::getCollectionValueType($property->type) ?? $property->type;
        $checkType->isSatisfiedBy(static function (TypeInfo $t) use (&$backingTypeId): bool {
            if ($t instanceof BackedEnumType) {
                $backingTypeId = $t->getBackingType()->getTypeIdentifier();

                return true;
            }

            return false;
        });

        if (null === $backingTypeId) {
            // non-backed enum without FromKey — cannot coerce
            return CoercionUtils::coerce(
                $property,
                static fn (mixed $val): mixed => $val,
                new Type(type: $enumClass),
                additionalConstraints: $constraints
            );
        }

        /** @var list<\BackedEnum> $allowed */
        $existing = [];

        foreach ($allowed as $case) {
            $existing[$case->value] = $case;
        }

        return CoercionUtils::coerce(
            $property,
            static function (mixed $val) use ($existing): mixed {
                if (!is_int($val) && !is_string($val)) {
                    return $val;
                }

                return $existing[$val] ?? $val;
            },
            new Type(type: $enumClass),
            TypeIdentifier::INT === $backingTypeId
                ? $this->integerCoercer->coerce($property, new Choice(choices: array_keys($existing)))
                : $this->stringCoercer->coerce($property, new Choice(choices: array_keys($existing))),
            additionalConstraints: $constraints
        );
    }
}
