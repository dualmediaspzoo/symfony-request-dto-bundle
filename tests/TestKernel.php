<?php

namespace DualMedia\DtoRequestBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use DualMedia\DtoRequestBundle\DtoBundle;
use DualMedia\DtoRequestBundle\Tests\Fixture\Controller\RequestKernelController;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @return list<BundleInterface>
     */
    #[\Override]
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
        $loader->load(__DIR__.'/../config/services.php');
        $loader->load(__DIR__.'/../config/services.test.php');

        $container->extension('framework', [
            'test' => true,
            'secret' => 'OpenSecret',
        ]);

        $container->extension('framework', [
            'router' => [
                'utf8' => true,
            ],
        ]);

        $container->services()
            ->set(RequestKernelController::class)
            ->public()
            ->tag('controller.service_arguments');

        $container->extension('doctrine', [
            'dbal' => [
                'driver' => 'pdo_sqlite',
                'path' => '%kernel.cache_dir%/test_db.sqlite',
            ],

            'orm' => [
                'auto_mapping' => true,
                'mappings' => [
                    'DualMedia\\DtoRequestBundle\\Tests\\Fixture\\Entity\\' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Fixture/Entity',
                        'prefix' => 'DualMedia\\DtoRequestBundle\\Tests\\Fixture\\Entity\\',
                        'alias' => 'app',
                    ],
                ],
            ],
        ]);
    }

    private function configureRoutes(
        RoutingConfigurator $routes
    ): void {
        $routes->add('test_valid', '/valid')
            ->controller([RequestKernelController::class, 'valid'])
            ->methods(['GET']);

        $routes->add('test_invalid', '/invalid')
            ->controller([RequestKernelController::class, 'invalid'])
            ->methods(['GET']);

        $routes->add('test_action', '/action')
            ->controller([RequestKernelController::class, 'action'])
            ->methods(['GET']);
    }
}
