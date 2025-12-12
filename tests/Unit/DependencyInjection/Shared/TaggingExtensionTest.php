<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection\Shared;

use DualMedia\DtoRequestBundle\DependencyInjection\Shared\TaggingExtension;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Group('unit')]
#[Group('dependency-injection')]
#[Group('shared')]
#[CoversClass(TaggingExtension::class)]
class TaggingExtensionTest extends TestCase
{
    #[TestWith(['interface', 'tag'])]
    #[TestWith(['other-interface', 'other-tag'])]
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
