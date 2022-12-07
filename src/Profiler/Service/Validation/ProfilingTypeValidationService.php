<?php

namespace DM\DtoRequestBundle\Profiler\Service\Validation;

use DM\DtoRequestBundle\Interfaces\Validation\TypeValidationInterface;
use DM\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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