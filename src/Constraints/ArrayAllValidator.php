<?php

namespace DualMedia\DtoRequestBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Checks if all dto objects are an array.
 */
class ArrayAllValidator extends ConstraintValidator
{
    public function validate(
        $value,
        Constraint $constraint
    ): void {
        // @codeCoverageIgnoreStart
        if (!$constraint instanceof ArrayAll) {
            throw new UnexpectedTypeException($constraint, ArrayAll::class);
        }
        // @codeCoverageIgnoreEnd

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \Traversable) {
            throw new UnexpectedValueException($value, 'iterable');
        }

        $violations = $this->context->getValidator()->validate($value, new Assert\All([
            'constraints' => new Assert\Type(['type' => 'array']),
        ]));

        if (!$violations->count()) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setCode($constraint::NOT_ALL_ELEMENTS_ARE_ARRAY_ERROR)
            ->setInvalidValue($value)
            ->addViolation();
    }
}
