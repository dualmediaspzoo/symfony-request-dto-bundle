<?php

namespace DualMedia\DtoRequestBundle;

use DualMedia\DtoRequestBundle\Dto\DependencyInjection\DetectionCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DtoBundle extends AbstractBundle
{
    public const string COERCER_TAG = 'dm.dto_bundle.coercer';

    public const string DTO_TAG = 'dm.dto_bundle.dto';

    public const string FIELD_HANDLER_TAG = 'dm.dto_bundle.field_handler';

    public const string DTO_LIST_PARAMETER = 'dm.dto_bundle.dto_class_list';

    protected string $extensionAlias = 'dm_dto';

    #[\Override]
    public function build(
        ContainerBuilder $container
    ): void {
        $container->addCompilerPass(new DetectionCompilerPass());
    }

    /**
     * @param array<array-key, mixed> $config
     */
    #[\Override]
    public function loadExtension(
        array $config,
        ContainerConfigurator $container,
        ContainerBuilder $builder
    ): void {
        $loader = new PhpFileLoader(
            $builder,
            new FileLocator(__DIR__.'/../config')
        );

        $loader->load('services.php');
    }
}
