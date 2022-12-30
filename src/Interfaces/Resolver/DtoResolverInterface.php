<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Resolver;

use DualMedia\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T of DtoInterface
 */
interface DtoResolverInterface
{
    /**
     * @param Request $request
     * @param class-string<T> $class
     *
     * @return T
     *
     * @throws InvalidTypeCountException
     * @throws ParameterNotSupportedException
     */
    public function resolve(
        Request $request,
        string $class
    ): DtoInterface;
}
