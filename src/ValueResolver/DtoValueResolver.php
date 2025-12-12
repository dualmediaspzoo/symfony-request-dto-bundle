<?php

namespace DualMedia\DtoRequestBundle\ValueResolver;

use DualMedia\DtoRequestBundle\Event\DtoResolvedEvent;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Interface\Resolver\DtoResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly DtoResolverInterface $dtoResolver,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return iterable<DtoInterface>
     */
    #[\Override]
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {
        $class = $argument->getType();

        if (null === $class || !is_subclass_of($class, DtoInterface::class)) {
            return [];
        }

        $this->eventDispatcher->dispatch(
            new DtoResolvedEvent(
                $object = $this->dtoResolver->resolve($request, $class)
            )
        );

        $object->setOptional($argument->isNullable());

        yield $object;
    }
}
