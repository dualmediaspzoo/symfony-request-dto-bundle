<?php

namespace DualMedia\DtoRequestBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use DualMedia\DtoRequestBundle\DtoBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DtoBundle(),
        ];
    }

    private function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder
    ): void {
        $loader->load(__DIR__.'/../config/services_test.php');

        $container->extension('framework', [
            'test' => true,
            'secret' => 'OpenSecret',
        ]);

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/test_db.sqlite',
            ],

            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'DualMedia\\DtoRequestBundle\\Tests\\Fixtures\\Entity\\' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Fixtures/Entity',
                        'prefix' => 'DualMedia\\DtoRequestBundle\\Tests\\Fixtures\\Entity\\',
                        'alias' => 'app',
                    ],
                ],
            ],
        ]);
    }
}
