<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isIdentifiedBy(TypeIdentifier::BOOL);
})]
class BooleanCoercer implements CoercerInterface
{
    #[\Override]
    public function coerce(
        Property $property,
        mixed $value
    ): Result {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $index => $val) {
            if ('null' === $val) {
                $value[$index] = null;
            } elseif (in_array((string)$val, ['0', '1'], true)) {
                $value[$index] = (bool)((int)$val);
            } elseif (in_array((string)$val, ['true', 'false'], true)) {
                $value[$index] = 'true' == $val;
            }
        }

        $isCollection = TypeInfoUtils::isCollection($property->type);
        $typeConstraint = new Type(type: 'bool');

        return new Result(
            $isCollection ? $value : $value[0],
            $isCollection ? [new All([$typeConstraint])] : [$typeConstraint]
        );
    }
}
