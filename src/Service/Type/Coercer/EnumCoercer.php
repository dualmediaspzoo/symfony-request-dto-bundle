<?php

namespace DM\DtoRequestBundle\Service\Type\Coercer;

use DM\DtoRequestBundle\Attributes\Dto\FromKey;
use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DM\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use DM\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @implements CoercerInterface<\BackedEnum|null>
 */
class EnumCoercer implements CoercerInterface
{
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    private bool $fromKey = false;
    private string $class;

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType() && is_subclass_of($property->getFqcn(), Enum::class);
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): CoerceResult {
        $this->fromKey = $property->hasDtoAttribute(FromKey::class);
        $this->class = $property->getFqcn();
        $constraints = [new Choice(['choices' => $property->getEnumChoices()])];

        if ($property->isCollection()) {
            $constraints = [
                new All([
                    'constraints' => $constraints,
                ]),
            ];
        }
        $constraints = array_merge($constraints, $property->getConstraints());
        $violations = $this->validator->startContext()
            ->atPath($propertyPath)
            ->validate($value, $constraints)
            ->getViolations();

        if (!$property->isCollection()) {
            return new CoerceResult(
                0 === $violations->count() ? $this->createEnum($value) : null,
                $violations
            );
        }

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            Util::removeIndexByConstraintViolation($value, $propertyPath, $violation);
        }

        foreach ($value as $index => $val) {
            $value[$index] = $this->createEnum($val);
        }

        return new CoerceResult(
            $value,
            $violations
        );
    }

    /**
     * @param int|string|null $value
     *
     * @return Enum|null
     */
    private function createEnum(
        $value
    ): ?Enum {
        if (null === $value) {
            return null;
        }

        if ($this->fromKey) {
            // @phpstan-ignore-next-line
            return call_user_func([$this->class, $value]);
        }

        // @phpstan-ignore-next-line
        return call_user_func([$this->class, 'from'], $value);
    }
}
