<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Property;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static fn (Property $p) => 'int' === $p->type)]
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

        return new Result(
            $property->collection ? $value : $value[0],
            [new Type(type: 'int')]
        );
    }
}