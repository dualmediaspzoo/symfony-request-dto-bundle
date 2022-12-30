<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attributes\Dto\Format;
use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements CoercerInterface<\DateTimeImmutable|null>
 */
class DateTimeImmutableCoercer implements CoercerInterface
{
    private string $defaultDateFormat;
    private ValidatorInterface $validator;

    public function __construct(
        string $defaultDateFormat,
        ValidatorInterface $validator
    ) {
        $this->defaultDateFormat = $defaultDateFormat;
        $this->validator = $validator;
    }

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType() &&
            in_array($property->getFqcn(), [\DateTimeInterface::class, \DateTimeImmutable::class]);
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): CoerceResult {
        // php8
        $format = ($property->getFormat() ?? new Format())->format ?? $this->defaultDateFormat;
        $constraint = new DateTime(['format' => $format]);

        $violations = $this->validator->startContext()
            ->atPath($propertyPath)
            ->validate($value, $property->isCollection() ? new All(['constraints' => $constraint]) : $constraint)
            ->getViolations();

        if (is_array($value)) {
            foreach ($value as $index => $val) {
                if (false === ($time = \DateTimeImmutable::createFromFormat($format, $val))) {
                    unset($value[$index]);
                    continue;
                }

                $value[$index] = $time;
            }
        } else {
            if (null === $value || false === ($time = \DateTimeImmutable::createFromFormat($format, $value))) { // @phpstan-ignore-line
                $value = null;
            } else {
                $value = $time;
            }
        }

        return new CoerceResult(
            $value,
            $violations
        );
    }
}
