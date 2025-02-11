<?php

namespace DualMedia\DtoRequestBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Composite;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class WhenVisited extends Composite
{
    /**
     * @param list<Constraint> $constraints
     */
    public function __construct(
        public array $constraints = [],
    ) {
        $options['constraints'] = $constraints;
        parent::__construct($options);
    }

    public function getRequiredOptions(): array
    {
        return ['constraints'];
    }

    public function getTargets(): string|array
    {
        return [self::PROPERTY_CONSTRAINT];
    }

    protected function getCompositeOption(): string
    {
        return 'constraints';
    }
}
