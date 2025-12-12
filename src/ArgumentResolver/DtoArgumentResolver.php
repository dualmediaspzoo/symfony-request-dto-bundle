<?php

namespace DualMedia\DtoRequestBundle\ArgumentResolver;

use DualMedia\DtoRequestBundle\Event\DtoResolvedEvent;
use DualMedia\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoArgumentResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly DtoResolverInterface $resolverService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return iterable<DtoInterface>
     *
     * @throws InvalidTypeCountException
     * @throws ParameterNotSupportedException
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {
        if (null === $argument->getType()
            || !is_subclass_of($argument->getType(), DtoInterface::class)) {
            return [];
        }

        /** @var class-string<DtoInterface> $class */
        $class = $argument->getType();
        $this->eventDispatcher->dispatch(
            new DtoResolvedEvent(
                $object = $this->resolverService->resolve($request, $class)
            )
        );

        $object->setOptional($argument->isNullable());

        return [$object];
    }
}
