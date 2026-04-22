<?php

namespace DualMedia\DtoRequestBundle\Tests\Trait;

trait KernelAccessTrait
{
    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function getService(
        string $id
    ): mixed {
        return static::getContainer()->get($id);
    }
}
