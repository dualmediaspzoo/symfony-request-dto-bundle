<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection;

use DualMedia\DtoRequestBundle\DependencyInjection\DtoExtension;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Group('unit')]
#[Group('dependency-injection')]
#[CoversClass(DtoExtension::class)]
class DtoExtensionTest extends KernelTestCase
{
    public function testCreation(): void
    {
        $extension = new DtoExtension();
        $extension->load([], $this->createMock(ContainerBuilder::class));
        static::assertTrue(true);
    }
}
