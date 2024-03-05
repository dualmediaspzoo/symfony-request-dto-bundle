<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorServiceInterface;
use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @implements CoercerInterface<\BackedEnum|null>
 */
class EnumCoercer implements CoercerInterface
{
    /**
     * @use CoercerResultTrait<\BackedEnum|null>
     */
    use CoercerResultTrait;

    private FromKey|null $fromKey;

    /**
     * @var list<\BackedEnum>
     */
    private array $cases;

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly LabelProcessorServiceInterface $labelProcessorService
    ) {
    }

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType()
            && is_subclass_of($property->getFqcn() ?? '', \BackedEnum::class);
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value,
        bool $validatePropertyConstraints = false
    ): CoerceResult {
        $this->fromKey = $property->getDtoAttributes(FromKey::class)[0] ?? null;

        $this->cases = call_user_func([$property->getFqcn(), 'cases']); // @phpstan-ignore-line
        $constraints = [$this->getChoiceConstraint($property)];

        if ($property->isCollection()) {
            $constraints = [
                new All([
                    'constraints' => $constraints,
                ]),
            ];
        }

        if ($validatePropertyConstraints) {
            $constraints = array_merge($constraints, $property->getConstraints());
        }

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

        // since the incoming labels might be in a different format we need to read the value
        // and turn it into a "real" label
        if (null !== $this->fromKey) {
            $value = $this->labelProcessorService->getProcessor($this->fromKey->normalizer)->denormalize((string)$value);
        }

        foreach ($this->cases as $case) {
            $compare = null !== $this->fromKey ? $case->name : $case->value;

            if ($value === $compare) {
                return $case;
            }
        }

        return null;
    }

    private function getChoiceConstraint(
        Property $property
    ): Choice {
        $choices = $property->getEnumCases();

        if (null !== ($fromKey = ($property->getDtoAttributes(FromKey::class)[0] ?? null))) {
            /** @var FromKey $fromKey */
            $processor = $this->labelProcessorService->getProcessor($fromKey->normalizer);

            $choices = array_map(
                fn (\BackedEnum $e) => $processor->normalize($e->name),
                $choices
            );
        } else {
            $choices = array_map(
                fn (\BackedEnum $e) => $e->value,
                $choices
            );
        }

        return new Choice(['choices' => $choices]);
    }
}
