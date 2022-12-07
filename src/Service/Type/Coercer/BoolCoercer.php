<?php

namespace DM\DtoRequestBundle\Service\Type\Coercer;

use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DM\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements CoercerInterface<bool|null>
 */
class BoolCoercer implements CoercerInterface
{
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    public function supports(
        Property $property
    ): bool {
        return 'bool' === $property->getType();
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): CoerceResult {
        if (!is_array($value)) {
            $value = [$value];
        }

        foreach ($value as $index => $val) {
            if (in_array($val, ['0', '1'], false)) { // cast from int
                $value[$index] = (bool)((int)$val);
            } elseif (in_array($val, ['true', 'false'])) { // cast from text
                $value[$index] = 'true' == $val; // non-strict comparison so pure-boolean checks go through too
            }
        }

        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $property->isCollection() ? $value : $value[0],
            [new Type(['type' => 'bool'])]
        );
    }
}