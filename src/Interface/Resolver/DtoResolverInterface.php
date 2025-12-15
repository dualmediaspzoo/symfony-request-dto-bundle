<?php

namespace DualMedia\DtoRequestBundle\Interface\Resolver;

use DualMedia\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template T of DtoInterface
 */
interface DtoResolverInterface
{
    /**
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
