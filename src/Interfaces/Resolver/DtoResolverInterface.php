<?php

namespace DM\DtoRequestBundle\Interfaces\Resolver;

use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DM\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
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
