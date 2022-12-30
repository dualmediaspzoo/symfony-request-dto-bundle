<?php

namespace DualMedia\DtoRequestBundle\Profiler\Service\Validation;

use DualMedia\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DualMedia\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @extends AbstractWrapper<ConstraintViolationListInterface>
 */
class ProfilingTypeValidationService extends AbstractWrapper implements TypeValidationInterface
{
    private TypeValidationInterface $validation;

    public function __construct(
        TypeValidationInterface $validation,
        ?Stopwatch $stopwatch = null
    ) {
        $this->validation = $validation;
        parent::__construct($stopwatch);
    }

    /**
     * @phpstan-ignore-next-line
     */
    public function validateType(
        array &$values,
        array $properties
    ): ConstraintViolationListInterface {
        return $this->wrap(
            'validate.%d',
            function () use (&$values, $properties) {
                return $this->validation->validateType($values, $properties);
            }
        );
    }
}
