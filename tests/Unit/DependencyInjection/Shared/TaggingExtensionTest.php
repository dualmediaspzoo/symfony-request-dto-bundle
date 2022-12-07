<?php

namespace DM\DtoRequestBundle\Tests\Unit\DependencyInjection\Shared;

use DM\DtoRequestBundle\DependencyInjection\Shared\TaggingExtension;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TaggingExtensionTest extends TestCase
{
    /**
     * @testWith ["interface", "tag"]
     *           ["other-interface", "other-tag"]
     */
    public function testLoad(
        string $interface,
        string $tag
    ): void {
        $extension = new TaggingExtension([
            $interface => $tag,
        ]);

        $container = $this->createMock(ContainerBuilder::class);
        $child = $this->createMock(ChildDefinition::class);

        $interfaceCheck = $this->deferCallable(function (string $in) use ($interface) {
            $this->assertEquals($interface, $in);
        });

        $container->expects($this->once())
            ->method('registerForAutoconfiguration')
            ->willReturnCallback(function (...$args) use ($child, $interfaceCheck) {
                $interfaceCheck->set($args);

                return $child;
            });

        $tagCheck = $this->deferCallable(function (string $in) use ($tag) {
            $this->assertEquals($tag, $in);
        });

        $child->expects($this->once())
            ->method('addTag')
            ->willReturnCallback(function (...$args) use ($child, $tagCheck) {
                $tagCheck->set($args);

                return $child;
            });

        $extension->load([], $container);
    }
}
