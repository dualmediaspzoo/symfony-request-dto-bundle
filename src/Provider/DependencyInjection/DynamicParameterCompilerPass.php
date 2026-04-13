<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\DependencyInjection;

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DynamicParameterCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        if (!$container->hasDefinition(DynamicParameterRegistry::class)) {
            return;
        }

        /** @var array<string, string> $map */
        $map = [];
        /** @var array<string, string> $methods */
        $methods = [];

        foreach ($container->findTaggedServiceIds(DtoBundle::DYNAMIC_PARAMETER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                foreach ($tag['parameters'] as $parameter) {
                    if (array_key_exists($parameter, $map)) {
                        throw new \LogicException(sprintf(
                            'Parameter %s already registered by %s (attempted re-registration from %s)',
                            $parameter,
                            $map[$parameter],
                            $id
                        ));
                    }

                    $map[$parameter] = $id;
                    $methods[$parameter] = $tag['method'];
                }
            }
        }

        $referenceMap = [];

        foreach ($map as $id) {
            $referenceMap[$id] = new Reference($id);
        }

        $definition = $container->getDefinition(DynamicParameterRegistry::class);
        $definition->setArgument('$map', $map)
            ->setArgument('$methods', $methods)
            ->setArgument('$locator', new ServiceLocatorArgument($referenceMap));
    }
}
