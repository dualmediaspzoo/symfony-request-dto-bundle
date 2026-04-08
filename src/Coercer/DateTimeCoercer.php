<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\DateTime;
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
        mixed $value
    ): Result {
        $inner = $this->stringCoercer->coerce($property, $value);

        /** @var Format|null $format */
        $format = array_find($property->meta, static fn ($m) => $m instanceof Format);

        if (null !== $format) {
            $inner = new Result(
                $inner->value,
                [...$inner->constraints, new DateTime(format: $format->format)]
            );
        }

        $isCollection = TypeInfoUtils::isCollection($property->type);
        $values = is_array($inner->value) ? $inner->value : [$inner->value];

        foreach ($values as $index => $val) {
            if (!is_string($val)) {
                continue;
            }

            if (null !== $format) {
                $result = \DateTimeImmutable::createFromFormat($format->format, $val);
                $values[$index] = false !== $result ? $result : $val;
            } else {
                try {
                    $values[$index] = new \DateTimeImmutable($val);
                } catch (\Exception) {
                    // leave as-is, Type constraint will catch it
                }
            }
        }

        $typeConstraint = new Type(type: \DateTimeImmutable::class);

        return new Result(
            $isCollection ? $values : $values[0],
            $isCollection ? [new All([$typeConstraint])] : [$typeConstraint],
            $inner
        );
    }
}
