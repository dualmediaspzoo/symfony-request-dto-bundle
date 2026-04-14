<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DoctrineQueryCreator\QueryCreator;
use DualMedia\DoctrineQueryCreator\ReferenceHelper;
use Symfony\Contracts\Service\ResetInterface;

class EntityProviderRegistry implements ResetInterface
{
    /**
     * @var array<class-string, EntityProvider>
     */
    private array $providers = [];

    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly QueryCreator $queryCreator,
        private readonly ReferenceHelper $referenceHelper
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

            $this->providers[$class] = new EntityProvider(
                $class,
                $repository,
                $this->queryCreator,
                $this->referenceHelper
            );
        }

        return $this->providers[$class];
    }

    public function reset(): void
    {
        $this->providers = [];
    }
}
