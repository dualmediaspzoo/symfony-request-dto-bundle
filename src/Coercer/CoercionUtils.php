<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;

final class CoercionUtils
{
    /**
     * Builds a coercion Result with a per-element callback.
     *
     * @param \Closure(mixed): mixed $coerce transforms a single element
     * @param Constraint|list<Constraint> $constraints
     */
    public static function coerce(
        Property $property,
        \Closure $coerce,
        Constraint|array $constraints,
        Result|null $inner = null
    ): Result {
        $isCollection = TypeInfoUtils::isCollection($property->type);
        $constraintList = is_array($constraints) ? $constraints : [$constraints];

        return new Result(
            static function (mixed $value) use ($isCollection, $coerce): mixed {
                $values = is_array($value) ? $value : [$value];

                foreach ($values as $index => $val) {
                    $values[$index] = $coerce($val);
                }

                return $isCollection ? $values : $values[0];
            },
            $isCollection ? [new All($constraintList)] : $constraintList,
            $inner
        );
    }
}
