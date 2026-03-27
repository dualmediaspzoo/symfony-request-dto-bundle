<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Reflection\PropertyReflector;
use DualMedia\DtoRequestBundle\Reflection\Reflector;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('reflection')]
#[CoversClass(Reflector::class)]
class ReflectorTest extends TestCase
{
    private Reflector $service;

    protected function setUp(): void
    {
        $this->service = new Reflector(
            new PropertyReflector()
        );
    }

    public function testVerySimpleDto(): void
    {
        $results = $this->service->reflect(VerySimpleDto::class);
        print_r($results);
    }

    public function testComplexDto(): void
    {
        $results = $this->service->reflect(ComplexDto::class);
        print_r($results);
    }
}
