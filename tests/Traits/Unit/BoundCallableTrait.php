<?php

namespace DM\DtoRequestBundle\Tests\Traits\Unit;

use DM\DtoRequestBundle\Tests\Model\BoundCallable;

trait BoundCallableTrait
{
    /**
     * @var BoundCallable[]
     */
    protected array $callables = [];

    protected function assertBoundCallables(): void
    {
        foreach ($this->callables as $callable) {
            $callable();
        }
    }

    protected function deferCallable(
        callable $fn
    ): BoundCallable {
        return $this->callables[] = new BoundCallable($fn);
    }
}
