<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\Validator\Constraints\Type;

/**
 * This class is a fake-ish coercer.
 *
 * Since all GET inputs are treated as strings, this will <i>only</i> apply to pre-processed
 * values which should be simply validated for proper type requirements
 *
 * @implements CoercerInterface<string|null>
 */
class StringCoercer implements CoercerInterface
{
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    public function supports(
        Property $property
    ): bool {
        return 'string' === $property->getType();
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value,
        bool $validatePropertyConstraints = false
    ): CoerceResult {
        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $value,
            [new Type(['type' => 'string'])],
            $validatePropertyConstraints
        );
    }
}
