<?php

namespace DualMedia\DtoRequestBundle\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Composite;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class WhenVisited extends Composite
{
    /**
     * @param list<Constraint>|Constraint $constraints
     * @param list<string>|null $groups
     * @param array<string,mixed> $options
     */
    public function __construct(
        public array|Constraint $constraints = [],
        array|null $groups = null,
        array $options = []
    ) {
        $options['constraints'] = $constraints;

        if (!\is_array($options['constraints'])) {
            $options['constraints'] = [$options['constraints']];
        }

        if (null !== $groups) {
            $options['groups'] = $groups;
        }

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
