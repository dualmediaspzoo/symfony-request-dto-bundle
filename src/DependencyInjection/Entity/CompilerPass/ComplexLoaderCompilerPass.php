<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass;

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ComplexLoaderCompilerPass implements CompilerPassInterface
{
    public function process(
        ContainerBuilder $container
    ): void {
        // @codeCoverageIgnoreStart
        if (!$container->hasDefinition(ComplexLoaderService::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $args = [];

        foreach ($container->findTaggedServiceIds(DtoBundle::COMPLEX_LOADER_TAG) as $id => $tags) {
            $args[$id] = new Reference($id);
        }

        $container->getDefinition(ComplexLoaderService::class)->setArgument(0, $args);
    }
}
