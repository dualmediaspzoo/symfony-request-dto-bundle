<?php

namespace DualMedia\DtoRequestBundle\ArgumentResolver;

use DualMedia\DtoRequestBundle\Event\DtoResolvedEvent;
use DualMedia\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DualMedia\DtoRequestBundle\Exception\Type\InvalidTypeCountException;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoArgumentResolver implements ArgumentValueResolverInterface
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

    public function supports(
        Request $request,
        ArgumentMetadata $argument
    ): bool {
        return null !== $argument->getType()
            && is_subclass_of($argument->getType(), DtoInterface::class);
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
