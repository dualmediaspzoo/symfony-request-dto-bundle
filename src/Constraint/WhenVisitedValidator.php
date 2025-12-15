<?php

namespace DualMedia\DtoRequestBundle\Constraint;

use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class WhenVisitedValidator extends ConstraintValidator
{
    #[\Override]
    public function validate(
        mixed $value,
        Constraint $constraint
    ): void {
        if (!$constraint instanceof WhenVisited) {
            throw new UnexpectedTypeException($constraint, WhenVisited::class);
        }

        $context = $this->context;
        $object = $context->getObject();

        if (!$object instanceof AbstractDto) {
            throw new UnexpectedTypeException($object, AbstractDto::class);
        }

        if (null === ($propertyName = $context->getPropertyName()) || !$object->visited($propertyName)) {
            return;
        }

        $context->getValidator()->inContext($context)
            ->validate($value, $constraint->constraints);
    }
}
