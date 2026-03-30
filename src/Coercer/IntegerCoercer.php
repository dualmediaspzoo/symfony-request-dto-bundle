<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type as TypeModel;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeModel $p): bool {
    return 'int' === $p->type;
})]
class IntegerCoercer implements CoercerInterface
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
            } elseif (is_numeric($val) && !str_contains((string)$val, '.')) {
                $value[$index] = (int)$val;
            }
        }

        $typeConstraint = new Type(type: 'int');

        return new Result(
            $property->type->isCollection() ? $value : $value[0],
            $property->type->isCollection() ? [new All([$typeConstraint])] : [$typeConstraint]
        );
    }
}