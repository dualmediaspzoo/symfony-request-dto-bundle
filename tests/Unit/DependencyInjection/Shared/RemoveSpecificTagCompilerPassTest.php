<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection\Shared;

use DualMedia\DtoRequestBundle\DependencyInjection\Shared\CompilerPass\RemoveSpecificTagCompilerPass;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity\DummyModelProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[Group('unit')]
#[Group('dependency-injection')]
#[Group('shared')]
#[CoversClass(RemoveSpecificTagCompilerPass::class)]
class RemoveSpecificTagCompilerPassTest extends AbstractCompilerPassTestCase
{
    public const TAG = 'test_tag';
    public const SERVICE_ID = 'affected';

    public function testNoop(): void
    {
        $this->container->addDefinitions([
            'unaffected' => (new Definition(DummyModelProvider::class))
                ->addTag(self::TAG),
        ]);

        $this->compile();

        $def = $this->container->getDefinition('unaffected');
        $this->assertCount(1, $def->getTag(self::TAG));
    }

    public function testRemove(): void
    {
        $this->container->addDefinitions([
            self::SERVICE_ID => (new Definition(DummyModelProvider::class))
                ->addTag(self::TAG),
            'unaffected' => (new Definition(DummyModelProvider::class))
                ->addTag(self::TAG),
        ]);

        $this->compile();

        $def = $this->container->getDefinition('unaffected');
        $this->assertCount(1, $def->getTag(self::TAG));

        $affected = $this->container->getDefinition(self::SERVICE_ID);
        $this->assertEmpty($affected->getTag(self::TAG));
    }

    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new RemoveSpecificTagCompilerPass(self::SERVICE_ID, self::TAG));
    }
}
