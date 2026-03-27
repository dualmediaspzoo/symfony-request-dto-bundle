<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type as TypeModel;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeModel $p): bool {
    return 'string' === $p->type;
})]
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
            $property->type->isCollection() ? $value : $value[0],
            [new Type(type: 'string')]
        );
    }
}