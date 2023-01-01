<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection\Entity;

use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\DoctrineRepositoryCompilerPass;
use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @group dependency-injection
 */
class DoctrineRepositoryCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addRequiredServices();
    }

    public function testRemoved(): void
    {
        $this->compile();

        $this->expectException(ServiceNotFoundException::class);
        $this->container->getDefinition(TargetProviderService::class);
    }

    public function testWithDoctrine(): void
    {
        $this->container->addDefinitions([
            'doctrine' => new Definition(),
        ]);

        $this->compile();
        $this->assertInstanceOf(Definition::class, $this->container->getDefinition(TargetProviderService::class));
    }

    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new DoctrineRepositoryCompilerPass());
    }

    private function addRequiredServices(): void
    {
        $this->container->addDefinitions([
            TargetProviderService::class => new Definition(TargetProviderService::class),
        ]);
    }
}
