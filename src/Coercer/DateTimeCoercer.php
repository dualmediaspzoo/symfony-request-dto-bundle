<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Time;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\Validator\Constraint;
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
        Property $property,
        Constraint|array $constraints = []
    ): Result {
        /** @var Format|null $format */
        $format = array_find($property->meta, static fn ($m) => $m instanceof Format);
        /** @var Time|null $time */
        $time = array_find($property->meta, static fn ($m) => $m instanceof Time);

        return CoercionUtils::coerce(
            $property,
            static function (mixed $val) use ($format, $time): mixed {
                if (!is_string($val)) {
                    return $val;
                }

                if (null !== $format) {
                    $result = \DateTimeImmutable::createFromFormat($format->format, $val);
                } else {
                    try {
                        $result = new \DateTimeImmutable($val);
                    } catch (\Exception) {
                        return $val; // Type constraint will catch it
                    }
                }

                if (false === $result) {
                    return $val;
                }

                if (null !== $time) {
                    $result = $result->setTime($time->interval->h, $time->interval->i, $time->interval->s);
                }

                return $result;
            },
            new Type(type: \DateTimeImmutable::class, message: 'This value is not valid.'),
            $this->stringCoercer->coerce($property),
            additionalConstraints: $constraints
        );
    }
}
