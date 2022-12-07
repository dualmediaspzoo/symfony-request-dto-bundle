<?php

namespace DM\DtoRequestBundle;

use DM\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ComplexLoaderCompilerPass;
use DM\DtoRequestBundle\DependencyInjection\Entity\CompilerPass\ProviderServiceCompilerPass;
use DM\DtoRequestBundle\DependencyInjection\Shared\CompilerPass\RemoveSpecificTagCompilerPass;
use DM\DtoRequestBundle\DependencyInjection\Shared\TaggingExtension;
use DM\DtoRequestBundle\DependencyInjection\Validation\CompilerPass\ValidationGroupAddingCompilerPass;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ComplexLoaderInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DM\DtoRequestBundle\Interfaces\Http\ActionValidatorInterface;
use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Interfaces\Validation\GroupProviderInterface;
use DM\DtoRequestBundle\Service\Http\ActionValidatorService;
use DM\DtoRequestBundle\Service\Resolver\DynamicResolverService;
use DM\DtoRequestBundle\Service\Type\CoercerService;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DtoBundle extends Bundle
{
    public const COERCER_TAG = 'dto_bundle.coercer';
    public const DYNAMIC_RESOLVER_TAG = 'dto_bundle.dynamic_resolver';

    public const HTTP_ACTION_VALIDATOR_TAG = 'dto_bundle.http_action_validator';

    public const ENTITY_PROVIDER_PRE_CONFIG_TAG = 'dto_bundle.entity_provider.pre_config';

    public const COMPLEX_LOADER_TAG = 'dto_bundle.complex_loader';
    public const GROUP_PROVIDER_TAG = 'dto_bundle.validation_group_provider';

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
        ]));

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
    }
}
