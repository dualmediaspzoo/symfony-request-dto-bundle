<?php

namespace DualMedia\DtoRequestBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This attribute may be used to attach constraints to a path in validation if the constraints themselves do not.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MappedToPath extends Constraint
{
    /**
     * @param list<Constraint>|Constraint $constraints
     * @param list<string>|null $groups
     */
    public function __construct(
        public string $path,
        public array|Constraint $constraints = [],
        $options = null,
        array $groups = null,
        $payload = null
    ) {
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
