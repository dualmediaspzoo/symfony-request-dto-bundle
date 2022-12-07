<?php

namespace DM\DtoRequestBundle\Tests\Unit\DependencyInjection;

use DM\DtoRequestBundle\DependencyInjection\DtoExtension;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
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
