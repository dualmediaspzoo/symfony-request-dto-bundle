<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection;

use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
class CacheReflectorTest extends KernelTestCase
{
    private CacheReflector $service;

    protected function setUp(): void
    {
        $this->service = static::getService(CacheReflector::class);
    }

    public function testComplexDto(): void
    {
        $result = $this->service->get(ComplexDto::class);
    }
}
