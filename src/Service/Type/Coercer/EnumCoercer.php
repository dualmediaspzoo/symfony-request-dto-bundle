<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use DualMedia\DtoRequestBundle\Util;
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

    /**
     * @var list<\BackedEnum>
     */
    private array $cases;

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType() &&
            is_subclass_of($property->getFqcn() ?? '', \BackedEnum::class);
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): CoerceResult {
        $this->fromKey = $property->hasDtoAttribute(FromKey::class);

        $this->cases = call_user_func([$property->getFqcn(), 'cases']); // @phpstan-ignore-line
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
                0 === $violations->count() ? $this->createEnum($value) : null, // @phpstan-ignore-line
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

    private function createEnum(
        int|string|null $value
    ): \BackedEnum|null {
        if (null === $value) {
            return null;
        }

        foreach ($this->cases as $case) {
            $compare = $this->fromKey ? $case->name : $case->value;

            if ($value === $compare) {
                return $case;
            }
        }

        return null;
    }
}
