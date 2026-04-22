<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * A constraint that cannot be serialized, used to test requiresRuntimeResolve.
 */
class UnserializableConstraint extends Constraint
{
    public \Closure $callback;

    public function __construct(
        \Closure $callback,
        array|null $groups = null,
        mixed $payload = null
    ) {
        parent::__construct(groups: $groups, payload: $payload);
        $this->callback = $callback;
    }
}
