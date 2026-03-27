<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection;

use DualMedia\DtoRequestBundle\Reflection\Reflector;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
abstract class AbstractReflectorTestCase extends KernelTestCase
{
    protected Reflector $service;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->service = static::getService(Reflector::class);
    }
}
