<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection\Entity;

use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ComplexLoaderCompilerPass;
use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ComplexLoaderServiceInterface;
use DualMedia\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity\DummyModelProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

#[Group('unit')]
#[Group('dependency-injection')]
#[Group('entity')]
#[CoversClass(ComplexLoaderService::class)]
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
