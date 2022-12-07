<?php

namespace DM\DtoRequestBundle\Service\Validation;

use DM\DtoRequestBundle\Interfaces\Type\CoercionServiceInterface;
use DM\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DM\DtoRequestBundle\Model\Type\Property as PropertyTypeModel;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class TypeValidationHelper implements TypeValidationInterface
{
    private CoercionServiceInterface $coercionService;

    public function __construct(
        CoercionServiceInterface $coercionService
    ) {
        $this->coercionService = $coercionService;
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, PropertyTypeModel> $properties
     *
     * @phpstan-ignore-next-line
     * @return ConstraintViolationListInterface
     */
    public function validateType(
        array &$values,
        array $properties
    ): ConstraintViolationListInterface {
        $subValues = [];
        $subProperties = [];

        // first validate subtypes
        foreach ($properties as $key => $property) {
            if (null === $property->getSubType()) {
                continue;
            }

            $subValues[$key] = $values[$key];
            $subProperties[$key] = (new PropertyTypeModel())
                ->setType($property->getSubType())
                ->setCollection($property->isCollection());
        }

        $list = new ConstraintViolationList();
        // coerce and copy sub-values
        foreach ($subValues as $key => $value) {
            if (null === ($result = $this->coercionService->coerce($key, $subProperties[$key], $value))) {
                continue;
            }

            $list->addAll($result->getViolations());
            $subValues[$key] = $result->getValue();
        }

        foreach ($subValues as $key => $value) {
            $values[$key] = $value;
        }
        unset($subValues);

        // coerce values
        foreach ($properties as $key => $property) {
            if (null === ($result = $this->coercionService->coerce($key, $property, $values[$key]))) {
                continue;
            }

            $list->addAll($result->getViolations());
            $values[$key] = $result->getValue();
        }

        return $list; // combined sub values and normal
    }
}
