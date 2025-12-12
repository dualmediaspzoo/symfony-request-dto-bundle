<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass;

use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessorService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class LabelProcessorCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        // @codeCoverageIgnoreStart
        if (!$container->hasDefinition(LabelProcessorService::class)) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $args = [];

        foreach ($container->findTaggedServiceIds(DtoBundle::LABEL_PROCESSOR_TAB) as $id => $tags) {
            $args[$id] = new Reference($id);
        }

        $container->getDefinition(LabelProcessorService::class)->setArgument(0, $args);
    }
}
