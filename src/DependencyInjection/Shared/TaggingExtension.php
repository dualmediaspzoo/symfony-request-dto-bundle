<?php

namespace DM\DtoRequestBundle\DependencyInjection\Shared;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class TaggingExtension extends Extension
{
    /**
     * @var array<string, string>
     */
    private array $map;

    /**
     * @param array<string, string> $map
     */
    public function __construct(
        array $map
    ) {
        $this->map = $map;
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
