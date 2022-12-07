<?php

namespace DM\DtoRequestBundle\Tests;

use DM\DtoRequestBundle\DtoBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DtoBundle(),
        ];
    }

    /**
     * 4.4 compatibility
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    public function configureContainer(
        ContainerBuilder $container,
        LoaderInterface $loader
    ): void {
        $loader->load(__DIR__.'/../config/services_test.php');
    }

    /**
     * 4.4 compatibility
     *
     * @param RoutingConfigurator $routes
     */
    protected function configureRoutes(
        RoutingConfigurator $routes
    ): void {
    }
}
