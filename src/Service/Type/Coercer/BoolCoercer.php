<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Interface\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements CoercerInterface<bool|null>
 */
class BoolCoercer implements CoercerInterface
{
    /**
     * @use CoercerResultTrait<bool|null>
     */
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    #[\Override]
    public function supports(
        Property $property
    ): bool {
        return 'bool' === $property->getType();
    }

    #[\Override]
    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value,
    ): CoerceResult {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $index => $val) {
            if ('null' === $val) {
                $value[$index] = null;
            }

            if (in_array((string)$val, ['0', '1'], true)) { // cast from int
                $value[$index] = (bool)((int)$val);
            } elseif (in_array((string)$val, ['true', 'false'], true)) { // cast from text
                $value[$index] = 'true' == $val; // non-strict comparison so pure-boolean checks go through too
            }
        }

        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $property->isCollection() ? $value : $value[0],
            [new Type(['type' => 'bool'])],
        );
    }
}
