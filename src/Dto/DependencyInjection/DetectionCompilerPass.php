<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\DependencyInjection;

use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DetectionCompilerPass implements CompilerPassInterface
{
    #[\Override]
    public function process(
        ContainerBuilder $container
    ): void {
        $classes = [];

        foreach ($container->findTaggedServiceIds(DtoBundle::DTO_TAG) as $id => $tags) {
            $classes[] = $container->getDefinition($id)->getClass();

            $container->removeDefinition($id);
        }

        $container->setParameter(DtoBundle::DTO_LIST_PARAMETER, array_values(array_unique($classes)));
    }
}
