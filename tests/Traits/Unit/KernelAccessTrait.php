<?php

namespace DualMedia\DtoRequestBundle\Tests\Traits\Unit;

trait KernelAccessTrait
{
    /**
     * @param string $id
     *
     * @return mixed
     */
    protected static function getService(
        string $id
    ) {
        return static::$container->get($id);
    }
}
