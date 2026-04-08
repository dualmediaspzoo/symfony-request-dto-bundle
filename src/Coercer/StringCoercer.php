<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isIdentifiedBy(TypeIdentifier::STRING);
})]
class StringCoercer implements CoercerInterface
{
    #[\Override]
    public function coerce(
        Property $property
    ): Result {
        return CoercionUtils::coerce(
            $property,
            static fn (mixed $val): mixed => 'null' === $val ? null : $val,
            new Type(type: 'string')
        );
    }
}
