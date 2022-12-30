<?php

namespace DualMedia\DtoRequestBundle\DependencyInjection\Shared;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TaggingExtension extends Extension
{
    /**
     * @param array<string, string> $map
     */
    public function __construct(
        private readonly array $map
    ) {
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     *
     * @return void
     * @phpstan-ignore-next-line
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        foreach ($this->map as $interface => $tag) {
            $container->registerForAutoconfiguration($interface)
                ->addTag($tag);
        }
    }
}
