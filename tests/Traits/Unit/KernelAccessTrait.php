<?php

namespace DualMedia\DtoRequestBundle\Tests\Traits\Unit;

trait KernelAccessTrait
{
    protected function getService(
        string $id
    ): object|null {
        return $this->getContainer()->get($id);
    }
}
