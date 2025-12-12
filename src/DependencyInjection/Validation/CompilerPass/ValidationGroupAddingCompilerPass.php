<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Validation\CompilerPass;

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Service\Validation\GroupProviderService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ValidationGroupAddingCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        // @codeCoverageIgnoreStart
        if (!$container->hasDefinition(GroupProviderService::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $args = [];

        foreach ($container->findTaggedServiceIds(DtoBundle::GROUP_PROVIDER_TAG) as $id => $tags) {
            $args[$id] = new Reference($id);
        }

        $container->getDefinition(GroupProviderService::class)->setArgument(0, $args);
    }
}
