<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\Validator\Constraints\Type;

#[Supports(static function (TypeInfo $type): bool {
    return $type->isIdentifiedBy(\DateTimeInterface::class)
        || $type->isIdentifiedBy(\DateTimeImmutable::class);
})]
class DateTimeCoercer implements CoercerInterface
{
    public function __construct(
        private readonly StringCoercer $stringCoercer
    ) {
    }

    #[\Override]
    public function coerce(
        Property $property
    ): Result {
        $inner = $this->stringCoercer->coerce($property);

        /** @var Format|null $format */
        $format = array_find($property->meta, static fn ($m) => $m instanceof Format);

        return CoercionUtils::coerce(
            $property,
            static function (mixed $val) use ($format): mixed {
                if (!is_string($val)) {
                    return $val;
                }

                if (null !== $format) {
                    $result = \DateTimeImmutable::createFromFormat($format->format, $val);

                    return false !== $result ? $result : $val;
                }

                try {
                    return new \DateTimeImmutable($val);
                } catch (\Exception) {
                    return $val; // Type constraint will catch it
                }
            },
            new Type(type: \DateTimeImmutable::class, message: 'This value is not valid.'),
            $inner
        );
    }
}
