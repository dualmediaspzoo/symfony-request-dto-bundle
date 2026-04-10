<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\DependencyInjection;

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

class DynamicParameterCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        if (!$container->hasDefinition(DynamicParameterRegistry::class)) {
            return;
        }

        $map = [];

        print_r($container->findTaggedServiceIds(DtoBundle::DYNAMIC_PARAMETER_TAG));

        foreach ($container->findTaggedServiceIds(DtoBundle::DYNAMIC_PARAMETER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                print_r($tag);
            }

            print_r($id);
        }

        $definition = $container->getDefinition(DynamicParameterRegistry::class);
        $definition->setArgument('$map', [])
            ->setArgument('$locator', service_locator([]));

        die();
    }
}
