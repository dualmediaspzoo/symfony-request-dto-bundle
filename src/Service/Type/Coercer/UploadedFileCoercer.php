<?php

namespace DualMedia\DtoRequestBundle\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Traits\Type\CoerceConstructWithValidatorTrait;
use DualMedia\DtoRequestBundle\Traits\Type\CoercerResultTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @implements CoercerInterface<UploadedFile|null>
 */
class UploadedFileCoercer implements CoercerInterface
{
    /**
     * @use CoercerResultTrait<UploadedFile|null>
     */
    use CoercerResultTrait;
    use CoerceConstructWithValidatorTrait;

    public function supports(
        Property $property
    ): bool {
        return 'object' === $property->getType() && (
            is_a($property->getFqcn(), UploadedFile::class, true) // @phpstan-ignore-line
            || is_subclass_of($property->getFqcn(), UploadedFile::class) // @phpstan-ignore-line
        );
    }

    public function coerce(
        string $propertyPath,
        Property $property,
        mixed $value
    ): CoerceResult {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->buildResult(
            $this->validator,
            $propertyPath,
            $property,
            $property->isCollection() ? $value : $value[0] ?? null,
            [new Type(['type' => UploadedFile::class])],
        );
    }
}
