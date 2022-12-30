<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection;

use DualMedia\DtoRequestBundle\DependencyInjection\DtoExtension;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DtoExtensionTest extends KernelTestCase
{
    public function testCreation(): void
    {
        $extension = new DtoExtension();
        $extension->load([], $this->createMock(ContainerBuilder::class));
        $this->assertTrue(true);
    }
}
