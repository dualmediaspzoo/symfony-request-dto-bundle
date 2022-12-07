<?php

namespace DM\DtoRequestBundle\Tests\Unit\DependencyInjection\Validation;

use DM\DtoRequestBundle\DependencyInjection\Validation\CompilerPass\ValidationGroupAddingCompilerPass;
use DM\DtoRequestBundle\DtoBundle;
use DM\DtoRequestBundle\Interfaces\Validation\GroupServiceInterface;
use DM\DtoRequestBundle\Service\Validation\GroupProviderService;
use DM\DtoRequestBundle\Tests\Fixtures\Service\Entity\DummyModelProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group dependency-injection
 */
class ValidationGroupAddingCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addRequiredServices();
    }

    public function testEmpty(): void
    {
        $this->compile();

        $definition = $this->container->getDefinition(GroupProviderService::class);

        $this->assertEmpty($definition->getArgument(0));
    }

    public function testTagged(): void
    {
        $this->container->addDefinitions([
            'not_affected' => new Definition(DummyModelProvider::class),
            'affected' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::GROUP_PROVIDER_TAG),
        ]);

        $this->compile();
        $definition = $this->container->getDefinition(GroupProviderService::class);

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
        $container->addCompilerPass(new ValidationGroupAddingCompilerPass());
    }

    private function addRequiredServices(): void
    {
        $this->container->addDefinitions([
            GroupProviderService::class => new Definition(GroupProviderService::class),
        ]);
        $this->container->addAliases([
            GroupServiceInterface::class => GroupProviderService::class,
        ]);
    }
}
