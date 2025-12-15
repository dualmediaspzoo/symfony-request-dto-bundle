<?php

namespace DualMedia\DtoRequestBundle\Tests\Traits\Unit;

use DualMedia\DtoRequestBundle\Tests\Model\BoundCallable;
use JetBrains\PhpStorm\Deprecated;

#[Deprecated('Up for removal with next update')]
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
