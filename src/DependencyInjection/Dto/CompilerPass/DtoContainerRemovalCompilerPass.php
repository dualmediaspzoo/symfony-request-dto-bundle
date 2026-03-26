<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Dto\CompilerPass;

use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DtoContainerRemovalCompilerPass implements CompilerPassInterface
{
    public function process(
        ContainerBuilder $container
    ): void {
        // todo: later read this list before removing into something so we can check it later and cache some data
        foreach ($container->findTaggedServiceIds(DtoBundle::DTO_TAG) as $id => $tags) {
            $container->removeDefinition($id);
        }
    }
}
