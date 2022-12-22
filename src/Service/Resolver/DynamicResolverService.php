<?php

namespace DM\DtoRequestBundle\Service\Resolver;

use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverInterface;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverServiceInterface;

class DynamicResolverService implements ResolverServiceInterface
{
    /**
     * @var list<ResolverInterface>
     */
    private array $resolvers;

    /**
     * @param \IteratorAggregate<array-key, ResolverInterface> $iterator
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function __construct(
        \IteratorAggregate $iterator
    ) {
        $this->resolvers = iterator_to_array($iterator->getIterator());
    }

    public function getSupportedParameters(): array
    {
        $arrays = [];

        foreach ($this->resolvers as $resolver) {
            $arrays[] = $resolver->getSupportedParameters();
        }

        return array_values(array_unique(array_merge(...$arrays)));
    }

    public function resolveParameter(
        string $name
    ): mixed {
        foreach ($this->resolvers as $resolver) {
            if (in_array($name, $resolver->getSupportedParameters())) {
                return $resolver->resolveParameter($name);
            }
        }

        throw new ParameterNotSupportedException(sprintf(
            "Parameter %s is not supported by any of the provided resolvers",
            $name
        ));
    }
}
