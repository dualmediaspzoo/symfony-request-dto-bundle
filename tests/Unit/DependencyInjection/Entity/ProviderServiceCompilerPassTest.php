<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\DependencyInjection\Entity;

use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ProviderServiceCompilerPass;
use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Exception\DependencyInjection\Entity\AttributeMissingException;
use DualMedia\DtoRequestBundle\Exception\DependencyInjection\Entity\DuplicateDefaultProviderException;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity\BadDummyModelProvider;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity\DummyModelProvider;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Service\Entity\NonDefaultDummyModelProvider;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group dependency-injection
 */
class ProviderServiceCompilerPassTest extends AbstractCompilerPassTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addRequiredServices();
    }

    public function testDuplicateDefaultProviders(): void
    {
        $this->container->addDefinitions([
            'dummy1' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
            'dummy2' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
        ]);

        try {
            $this->compile();
        } catch (DuplicateDefaultProviderException $e) {
            $this->assertEquals([
                DummyModel::class => [
                    'dummy1',
                    'dummy2',
                ],
            ], $e->getDuplicates());
        } catch (\Throwable $e) {
            $this->fail('Invalid exception caught - '.get_class($e));
        }
    }

    public function testBadProvider(): void
    {
        $this->container->addDefinitions([
            'dummy' => (new Definition(BadDummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
        ]);

        try {
            $this->compile();
        } catch (AttributeMissingException $e) {
            $this->assertEquals(BadDummyModelProvider::class, $e->getClass());
        } catch (\Throwable $e) {
            $this->fail('Invalid exception caught - '.get_class($e));
        }
    }

    public function testValidSetup(): void
    {
        $this->container->addDefinitions([
            'default' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
            'non_default' => (new Definition(NonDefaultDummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
        ]);

        $this->compile();

        $default = $this->container->getDefinition('default');
        $nonDefault = $this->container->getDefinition('non_default');

        $this->assertFalse($default->hasTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG));
        $this->assertFalse($nonDefault->hasTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG));

        $service = $this->container->getDefinition(EntityProviderService::class);

        $this->assertCount(2, $arg = $service->getArgument(0));

        foreach (['default', 'non_default'] as $index) {
            $this->assertArrayHasKey($index, $arg);

            /** @var list<array{0: Reference, 1: class-string, 2: bool}> $params */
            $this->assertCount(1, $params = $arg[$index]);

            $this->assertInstanceOf(Reference::class, $params[0][0]);
            $this->assertEquals($index, (string)$params[0][0]);
            $this->assertEquals(DummyModel::class, $params[0][1]);
            $this->assertEquals('default' === $index, $params[0][2]);
        }
    }

    public function testValidPreSetup(): void
    {
        $definition = $this->container->getDefinition(EntityProviderService::class);
        $definition->setArgument(0, [
            'some_service_injected_previously' => [
                [new Reference('some_service_injected_previously'), DummyModel::class, false],
            ],
            'non_default' => [
                [new Reference('non_default'), DummyModel::class, false],
            ],
        ]);

        $this->container->addDefinitions([
            'default' => (new Definition(DummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
            'non_default' => (new Definition(NonDefaultDummyModelProvider::class))
                ->addTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG),
        ]);

        $this->compile();

        $default = $this->container->getDefinition('default');
        $nonDefault = $this->container->getDefinition('non_default');

        $this->assertFalse($default->hasTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG));
        $this->assertFalse($nonDefault->hasTag(DtoBundle::ENTITY_PROVIDER_PRE_CONFIG_TAG));

        $service = $this->container->getDefinition(EntityProviderService::class);

        $this->assertCount(3, $arg = $service->getArgument(0));

        foreach (['default', 'non_default', 'some_service_injected_previously'] as $index) {
            $this->assertArrayHasKey($index, $arg);

            /** @var list<array{0: Reference, 1: class-string, 2: bool}> $params */
            $this->assertCount(1, $params = $arg[$index]);

            $this->assertInstanceOf(Reference::class, $params[0][0]);
            $this->assertEquals($index, (string)$params[0][0]);
            $this->assertEquals(DummyModel::class, $params[0][1]);
            $this->assertEquals('default' === $index, $params[0][2]);
        }
    }

    protected function registerCompilerPass(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new ProviderServiceCompilerPass());
    }

    private function addRequiredServices(): void
    {
        $this->container->addDefinitions([
            EntityProviderService::class => new Definition(EntityProviderService::class),
        ]);
        $this->container->addAliases([
            ProviderServiceInterface::class => EntityProviderService::class,
        ]);
    }
}
