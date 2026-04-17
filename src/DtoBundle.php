<?php

namespace DualMedia\DtoRequestBundle;

use DualMedia\DtoRequestBundle\Dto\DependencyInjection\DetectionCompilerPass;
use DualMedia\DtoRequestBundle\Provider\Attribute\AsDynamicProvider;
use DualMedia\DtoRequestBundle\Provider\DependencyInjection\DynamicParameterCompilerPass;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use DualMedia\DtoRequestBundle\Resolve\Interface\LabelProcessorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DtoBundle extends AbstractBundle
{
    public const string COERCER_TAG = 'dm.dto_bundle.coercer';
    public const string DTO_TAG = 'dm.dto_bundle.dto';
    public const string FIELD_HANDLER_TAG = 'dm.dto_bundle.field_handler';
    public const string OBJECT_PROVIDER_TAG = 'dm_dto_bundle.object_provider';
    public const string LABEL_PROCESSOR_TAG = 'dm_dto_bundle.label_processor';
    public const string GROUP_PROVIDER_TAG = 'dm_dto_bundle.group_provider';
    public const string DYNAMIC_PARAMETER_TAG = 'dm_dto_bundle.dynamic_parameter_provider';

    public const string DTO_LIST_PARAMETER = 'dm.dto_bundle.dto_class_list';

    protected string $extensionAlias = 'dm_dto';

    #[\Override]
    public function build(
        ContainerBuilder $container
    ): void {
        $container->registerAttributeForAutoconfiguration(AsDynamicProvider::class, static function (ChildDefinition $definition, AsDynamicProvider $attribute, \Reflector $reflector): void {
            assert($reflector instanceof \ReflectionMethod);
            $definition->addTag(self::DYNAMIC_PARAMETER_TAG, [
                'parameters' => (array)$attribute->parameter,
                'method' => $reflector->getName(),
            ]);
        });

        $container->registerForAutoconfiguration(LabelProcessorInterface::class)
            ->addTag(self::LABEL_PROCESSOR_TAG);

        $container->registerForAutoconfiguration(GroupProviderInterface::class)
            ->addTag(self::GROUP_PROVIDER_TAG);

        $container->registerForAutoconfiguration(ProviderInterface::class)
            ->addTag(self::OBJECT_PROVIDER_TAG);

        $container->addCompilerPass(new DetectionCompilerPass());
        $container->addCompilerPass(new DynamicParameterCompilerPass());
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

        if (true === $builder->getParameter('kernel.debug')) {
            $loader->load('services.dev.php');
        }

        if ('test' === $builder->getParameter('kernel.environment')) {
            $loader->load('services.test.php');
        }

        if (interface_exists(\Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface::class)) {
            $loader->load('services_nelmio.php');
        }
    }
}
