<?php

namespace DualMedia\DtoRequestBundle;

use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ComplexLoaderCompilerPass;
use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\DoctrineRepositoryCompilerPass;
use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\LabelProcessorCompilerPass;
use DualMedia\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ProviderServiceCompilerPass;
use DualMedia\DtoRequestBundle\DependencyInjection\Shared\CompilerPass\RemoveSpecificTagCompilerPass;
use DualMedia\DtoRequestBundle\DependencyInjection\Shared\TaggingExtension;
use DualMedia\DtoRequestBundle\DependencyInjection\Validation\CompilerPass\ValidationGroupAddingCompilerPass;
use DualMedia\DtoRequestBundle\Interface\Dynamic\ResolverInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ComplexLoaderInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\LabelProcessorInterface;
use DualMedia\DtoRequestBundle\Interface\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Interface\Http\ActionValidatorInterface;
use DualMedia\DtoRequestBundle\Interface\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Interface\Validation\GroupProviderInterface;
use DualMedia\DtoRequestBundle\Service\Http\ActionValidatorService;
use DualMedia\DtoRequestBundle\Service\Nelmio\DtoOADescriber;
use DualMedia\DtoRequestBundle\Service\Resolver\DynamicResolverService;
use DualMedia\DtoRequestBundle\Service\Type\CoercerService;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class DtoBundle extends AbstractBundle
{
    public const string COERCER_TAG = 'dto_bundle.coercer';
    public const string DYNAMIC_RESOLVER_TAG = 'dto_bundle.dynamic_resolver';

    public const string HTTP_ACTION_VALIDATOR_TAG = 'dto_bundle.http_action_validator';

    public const string ENTITY_PROVIDER_PRE_CONFIG_TAG = 'dto_bundle.entity_provider.pre_config';

    public const string COMPLEX_LOADER_TAG = 'dto_bundle.complex_loader';
    public const string GROUP_PROVIDER_TAG = 'dto_bundle.validation_group_provider';

    public const string LABEL_PROCESSOR_TAB = 'dto_bundle.label_processor';

    protected string $extensionAlias = 'dm_dto';

    #[\Override]
    public function build(
        ContainerBuilder $container
    ): void {
        $container->registerExtension(new TaggingExtension([
            ProviderInterface::class => self::ENTITY_PROVIDER_PRE_CONFIG_TAG,
            GroupProviderInterface::class => self::GROUP_PROVIDER_TAG,
            CoercerInterface::class => self::COERCER_TAG,
            ResolverInterface::class => self::DYNAMIC_RESOLVER_TAG,
            ComplexLoaderInterface::class => self::COMPLEX_LOADER_TAG,
            ActionValidatorInterface::class => self::HTTP_ACTION_VALIDATOR_TAG,
            LabelProcessorInterface::class => self::LABEL_PROCESSOR_TAB,
        ]));

        // Doctrine autoconfigure
        $container->addCompilerPass(new DoctrineRepositoryCompilerPass(), PassConfig::TYPE_OPTIMIZE, 99);

        // entity provider
        $container->addCompilerPass(new ProviderServiceCompilerPass(), PassConfig::TYPE_OPTIMIZE, 100);

        // validation groups
        $container->addCompilerPass(new ValidationGroupAddingCompilerPass());

        // http action validators
        $container->addCompilerPass(new RemoveSpecificTagCompilerPass(
            ActionValidatorService::class,
            self::HTTP_ACTION_VALIDATOR_TAG
        ));

        // coercers
        $container->addCompilerPass(new RemoveSpecificTagCompilerPass(
            CoercerService::class,
            self::COERCER_TAG
        ));

        // dynamic resolvers
        $container->addCompilerPass(new RemoveSpecificTagCompilerPass(
            DynamicResolverService::class,
            self::DYNAMIC_RESOLVER_TAG
        ));

        // complex loaders
        $container->addCompilerPass(new ComplexLoaderCompilerPass());

        // label processors
        $container->addCompilerPass(new LabelProcessorCompilerPass());
    }

    /**
     * @param array<string, mixed> $config
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

        /** @psalm-suppress UndefinedDocblockClass */
        if ($builder->getParameter('kernel.debug')) {
            $loader->load('services_dev.php');
        }

        // @codeCoverageIgnoreStart
        if (!interface_exists(RouteDescriberInterface::class)) {
            // remove the describer if Nelmio is unavailable
            $builder->removeDefinition(DtoOADescriber::class);
        }
        // @codeCoverageIgnoreEnd
    }
}
