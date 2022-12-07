<?php

namespace DM\DtoRequestBundle\DependencyInjection;

use DM\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DtoExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @phpstan-ignore-next-line
     * @throws \Exception
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config')
        );

        /** @psalm-suppress UndefinedDocblockClass */
        if ($container->getParameter('kernel.debug')) {
            $loader->load('services_dev.php');
        } else {
            $loader->load('services.php');
        }

        // @codeCoverageIgnoreStart
        if (!interface_exists(RouteDescriberInterface::class)) {
            // remove the describer if Nelmio is unavailable
            $container->removeDefinition(DtoOADescriber::class);
        }
        // @codeCoverageIgnoreEnd
    }
}
