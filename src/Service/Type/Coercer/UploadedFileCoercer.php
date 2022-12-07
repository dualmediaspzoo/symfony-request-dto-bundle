<?php

namespace DM\DtoRequestBundle\Service\Type\Coercer;

use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DM\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements CoercerInterface<UploadedFile|null>
 */
class UploadedFileCoercer implements CoercerInterface
{
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType() && (
            is_a($property->getFqcn(), UploadedFile::class, true) ||
            is_subclass_of($property->getFqcn(), UploadedFile::class)
        );
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        $value
    ): CoerceResult {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $property->isCollection() ? $value : $value[0] ?? null,
            [new Type(['type' => UploadedFile::class])]
        );
    }
}
