<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isIdentifiedBy(TypeIdentifier::FLOAT);
})]
class FloatCoercer implements CoercerInterface
{
    #[\Override]
    public function coerce(
        Property $property,
        Constraint|array $constraints = []
    ): Result {
        return CoercionUtils::coerce(
            $property,
            static function (mixed $val): mixed {
                if ('null' === $val) {
                    return null;
                }

                if (is_numeric($val)) {
                    return (float)$val;
                }

                return $val;
            },
            new Type(type: 'float'),
            additionalConstraints: $constraints
        );
    }
}
