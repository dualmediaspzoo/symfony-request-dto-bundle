<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection;

use DualMedia\DtoRequestBundle\Reflection\Reflector;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
#[CoversClass(Reflector::class)]
class ReflectorTest extends KernelTestCase
{
    private Reflector $service;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->service = static::getService(Reflector::class);
    }

    public function testComplex(): void
    {
        $result = $this->service->reflect(ComplexDto::class);
        print_r($result);
    }
}
