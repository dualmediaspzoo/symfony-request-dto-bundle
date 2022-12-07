<?php

namespace DM\DtoRequestBundle\Service\Type\Coercer;

use DM\DtoRequestBundle\Annotations\Dto\Format;
use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
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
            if ($value === null || false === ($time = \DateTimeImmutable::createFromFormat($format, $value))) {
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
