<?php

namespace DM\DtoRequestBundle\DependencyInjection\Shared\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RemoveSpecificTagCompilerPass implements CompilerPassInterface
{
    private string $id;
    private string $tag;

    public function __construct(
        string $id,
        string $tag
    ) {
        $this->id = $id;
        $this->tag = $tag;
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
