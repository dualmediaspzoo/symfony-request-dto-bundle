<?php

namespace DM\DtoRequestBundle\Tests\Traits\Unit;

use PHPUnit\Framework\MockObject\MockObject;

trait MockWithCustomMethodsTrait
{
    protected function createMockWithMethods(
        string $originalClassName,
        array $methods = []
    ): MockObject {
        return $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods($methods)
            ->getMock();
    }
}
