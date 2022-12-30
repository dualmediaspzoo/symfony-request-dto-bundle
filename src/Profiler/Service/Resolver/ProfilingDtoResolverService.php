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
 * @implements DtoResolverInterface<T>
 */
class ProfilingDtoResolverService extends AbstractWrapper implements DtoResolverInterface
{
    private DtoResolverInterface $resolver;

    public function __construct(
        DtoResolverInterface $resolver,
        ?Stopwatch $stopwatch = null
    ) {
        $this->resolver = $resolver;
        parent::__construct($stopwatch);
    }

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
