<?php

namespace DualMedia\DtoRequestBundle\ValueResolver;

use DualMedia\DtoRequestBundle\Event\DtoResolvedEvent;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Interfaces\Resolver\DtoResolverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/** @psalm-suppress UndefinedClass */
if (interface_exists(\Symfony\Component\HttpKernel\Controller\ValueResolverInterface::class)) {
    class DtoValueResolver implements \Symfony\Component\HttpKernel\Controller\ValueResolverInterface
    {
        public function __construct(
            private DtoResolverInterface $dtoResolver,
            private EventDispatcherInterface $eventDispatcher
        ) {
        }

        /**
         * @return iterable<DtoInterface>
         */
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
}
