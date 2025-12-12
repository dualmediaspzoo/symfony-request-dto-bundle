<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass;

use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineRepositoryCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        // @codeCoverageIgnoreStart
        if (!$container->hasDefinition(TargetProviderService::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        if (!$container->hasDefinition('doctrine')) {
            $container->removeDefinition(TargetProviderService::class);
        }
    }
}
