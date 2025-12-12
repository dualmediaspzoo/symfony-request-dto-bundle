<?php

namespace DualMedia\DtoRequestBundle\Profiler\Service\Resolver;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Interface\Resolver\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @extends AbstractWrapper<DtoInterface>
 *
 * @implements DtoResolverInterface<DtoInterface>
 */
class ProfilingDtoResolverService extends AbstractWrapper implements DtoResolverInterface
{
    /**
     * @param DtoResolverInterface<DtoInterface> $resolver
     */
    public function __construct(
        private readonly DtoResolverInterface $resolver,
        Stopwatch|null $stopwatch = null
    ) {
        parent::__construct($stopwatch);
    }

    #[\Override]
    public function resolve(
        Request $request,
        string $class
    ): DtoInterface {
        return $this->wrap(
            'resolve:%d:'.$class,
            fn () => $this->resolver->resolve($request, $class)
        );
    }
}
