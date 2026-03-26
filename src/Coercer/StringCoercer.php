<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Property;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static fn (Property $p) => 'string' === $p->type)]
class StringCoercer implements CoercerInterface
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
            }
        }

        return new Result(
            $property->collection ? $value : $value[0],
            [new Type(type: 'string')]
        );
    }
}