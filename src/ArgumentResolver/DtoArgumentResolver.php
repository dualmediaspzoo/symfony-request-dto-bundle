<?php

namespace DM\DtoRequestBundle\ArgumentResolver;

use DM\DtoRequestBundle\Event\DtoResolvedEvent;
use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DM\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use DM\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoArgumentResolver implements ValueResolverInterface
{
    private DtoResolverInterface $dtoResolver;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        DtoResolverInterface $resolverService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->dtoResolver = $resolverService;
        $this->eventDispatcher = $eventDispatcher;
    }


    /**
     * @param Request $request
     * @param ArgumentMetadata $argument
     *
     * @return iterable<DtoInterface>
     *
     * @throws InvalidTypeCountException
     * @throws ParameterNotSupportedException
     */
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {
        if (DtoInterface::class === $argument->getType() ||
            !is_subclass_of($argument->getType(), DtoInterface::class)) {
            return [];
        }

        /** @var class-string<DtoInterface> $class */
        $class = $argument->getType();
        $this->eventDispatcher->dispatch(
            new DtoResolvedEvent(
                $object = $this->dtoResolver->resolve($request, $class)
            )
        );

        $object->setOptional($argument->isNullable());

        yield $object;
    }
}
