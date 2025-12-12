<?php

namespace DualMedia\DtoRequestBundle\Profiler\Service\Resolver;

use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Profiler\AbstractWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @template T of DtoInterface
 *
 * @extends AbstractWrapper<T>
 *
 * @implements DtoResolverInterface<T>
 */
class ProfilingDtoResolverService extends AbstractWrapper implements DtoResolverInterface
{
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
