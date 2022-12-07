<?php

namespace DM\DtoRequestBundle\Tests\Unit\DependencyInjection\Entity;

use DM\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ComplexLoaderCompilerPass;
use DM\DtoRequestBundle\DtoBundle;
use DM\DtoRequestBundle\Interfaces\Entity\ComplexLoaderServiceInterface;
use DM\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use DM\DtoRequestBundle\Tests\Fixtures\Service\Entity\DummyModelProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group dependency-injection
 */
class ComplexLoaderCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addRequiredServices();
    }

    public function testEmpty(): void
    {
        $this->compile();

        $definition = $this->container->getDefinition(ComplexLoaderService::class);

        $this->assertEmpty($definition->getArgument(0));
    }

    public function testTagged(): void
    {
        $this->container->addDefinitions([
            'not_affected' => new Definition(DummyModelProvider::class),
            'affected' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::COMPLEX_LOADER_TAG),
        ]);

        $this->compile();
        $definition = $this->container->getDefinition(ComplexLoaderService::class);

        $this->assertCount(1, $arg = $definition->getArgument(0));
        $this->assertArrayHasKey('affected', $arg);

        /** @var Reference $ref */
        $ref = $arg['affected'];
        $this->assertInstanceOf(Reference::class, $ref);
        $this->assertEquals('affected', (string)$ref);
    }

    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new ComplexLoaderCompilerPass());
    }

    private function addRequiredServices(): void
    {
        $this->container->addDefinitions([
            ComplexLoaderService::class => new Definition(ComplexLoaderService::class),
        ]);
        $this->container->addAliases([
            ComplexLoaderServiceInterface::class => ComplexLoaderService::class,
        ]);
    }
}
