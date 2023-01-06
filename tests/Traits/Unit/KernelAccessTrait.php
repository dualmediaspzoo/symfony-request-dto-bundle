<?php

namespace DualMedia\DtoRequestBundle\Tests\Traits\Unit;

trait KernelAccessTrait
{
    /**
     * @param string $id
     *
     * @return object|null
     */
    protected function getService(
        string $id
    ): ?object {
        return $this->getContainer()->get($id);
    }
}
