<?php

namespace DM\DtoRequestBundle\Interfaces\Resolver;

use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DM\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Component\HttpFoundation\Request;

interface DtoResolverInterface
{
    /**
     * @template T of DtoInterface
     *
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
