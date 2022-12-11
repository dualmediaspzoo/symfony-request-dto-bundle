<?php

namespace DM\DtoRequestBundle\DependencyInjection\Shared\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveSpecificTagCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $tag
    ) {
    }

    public function process(
        ContainerBuilder $container
    ): void {
        if (!$container->hasDefinition($this->id)) {
            return; // service inactive
        }

        $container->getDefinition($this->id)->clearTag($this->tag);
    }
}
