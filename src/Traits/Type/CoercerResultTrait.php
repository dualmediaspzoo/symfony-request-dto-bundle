<?php

namespace DM\DtoRequestBundle\Traits\Type;

use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait CoercerResultTrait
{
    /**
     * @param ValidatorInterface $validator
     * @param string $propertyPath
     * @param Property $property
     * @param mixed $value
     * @param Constraint[] $constraints
     *
     * @return CoerceResult
     */
    private function buildResult(
        ValidatorInterface $validator,
        string $propertyPath,
        Property $property,
        mixed $value,
        array $constraints
    ): CoerceResult {
        if ($property->isCollection()) {
            $constraints = [
                new All([
                    'constraints' => $constraints,
                ]),
            ];
        }
        $constraints = array_merge($constraints, $property->getConstraints());
        $violations = $validator->startContext()
            ->atPath($propertyPath)
            ->validate($value, $constraints)
            ->getViolations();

        if (!$property->isCollection()) {
            return new CoerceResult(
                0 === $violations->count() ? $value : null,
                $violations
            );
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            Util::removeIndexByConstraintViolation($value, $propertyPath, $violation);
        }

        return new CoerceResult(
            $value,
            $violations
        );
    }
}
