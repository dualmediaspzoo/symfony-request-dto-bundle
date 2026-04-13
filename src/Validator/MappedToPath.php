<?php

namespace DualMedia\DtoRequestBundle\Validator;

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
        array|null $groups = null,
        mixed $payload = null
    ) {
        parent::__construct(groups: $groups, payload: $payload);
    }

    #[\Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
