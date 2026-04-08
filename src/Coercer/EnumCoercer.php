<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\Type\BackedEnumType;
use Symfony\Component\TypeInfo\Type\EnumType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isSatisfiedBy(static fn (TypeInfo $t): bool => $t instanceof EnumType);
})]
class EnumCoercer implements CoercerInterface
{
    public function __construct(
        private readonly StringCoercer $stringCoercer,
        private readonly IntegerCoercer $integerCoercer
    ) {
    }

    #[\Override]
    public function coerce(
        Property $property
    ): Result {
        $fromKey = null !== array_find($property->meta, static fn ($m) => $m instanceof FromKey);

        /** @var class-string<\UnitEnum> $enumClass */
        $enumClass = TypeInfoUtils::getClassName($property->type)
            ?? TypeInfoUtils::getCollectionValueClassName($property->type);

        if ($fromKey) {
            return CoercionUtils::coerce(
                $property,
                static function (mixed $val) use ($enumClass): mixed {
                    if (!is_string($val)) {
                        return $val;
                    }

                    return array_find(
                        $enumClass::cases(),
                        static fn (\UnitEnum $case) => $case->name === $val
                    ) ?? $val;
                },
                new Type(type: $enumClass),
                $this->stringCoercer->coerce($property)
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
                new Type(type: $enumClass)
            );
        }

        /** @var class-string<\BackedEnum> $enumClass */

        return CoercionUtils::coerce(
            $property,
            static function (mixed $val) use ($enumClass): mixed {
                if (!is_int($val) && !is_string($val)) {
                    return $val;
                }

                return $enumClass::tryFrom($val) ?? $val;
            },
            new Type(type: $enumClass),
            TypeIdentifier::INT === $backingTypeId
                ? $this->integerCoercer->coerce($property)
                : $this->stringCoercer->coerce($property)
        );
    }
}
