<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Interface\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements CoercerInterface<float|null>
 */
class FloatCoercer implements CoercerInterface
{
    /**
     * @use CoercerResultTrait<float|null>
     */
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    #[\Override]
    public function supports(
        Property $property
    ): bool {
        return 'float' === $property->getType();
    }

    #[\Override]
    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value
    ): CoerceResult {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $index => $val) {
            if ('null' === $val) {
                $value[$index] = null;
            }

            if (is_numeric($val)) {
                $value[$index] = (float)$val;
            }
        }

        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $property->isCollection() ? $value : $value[0],
            [new Type(['type' => 'float'])],
        );
    }
}
