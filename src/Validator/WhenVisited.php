<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class WhenVisited extends Constraint
{
    /**
     * @param list<Constraint>|Constraint $constraint
     * @param list<string>|null $groups
     */
    public function __construct(
        public readonly array|Constraint $constraint,
        array|null $groups = null
    ) {
        parent::__construct(groups: $groups);
    }
}
