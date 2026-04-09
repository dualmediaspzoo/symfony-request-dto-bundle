<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntityProviderRegistry
{
    /**
     * @var array<class-string, EntityProvider>
     */
    private array $providers = [];

    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    /**
     * @param class-string $class
     */
    public function get(
        string $class
    ): EntityProvider {
        if (!array_key_exists($class, $this->providers)) {
            $repository = $this->registry->getRepository($class);
            assert($repository instanceof EntityRepository);

            $this->providers[$class] = new EntityProvider($class, $repository);
        }

        return $this->providers[$class];
    }
}
