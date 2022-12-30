<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass;

use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineRepositoryCompilerPass implements CompilerPassInterface
{
    public function process(
        ContainerBuilder $container
    ): void {
        // @codeCoverageIgnoreStart
        if (!$container->hasDefinition(EntityProviderService::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd
    }
}
