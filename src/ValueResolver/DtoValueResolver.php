<?php

namespace DualMedia\DtoRequestBundle\ValueResolver;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Event\ResolvedEvent;
use DualMedia\DtoRequestBundle\Parameter\Attribute\AllowInvalid;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly DtoResolver $dtoResolver,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @return iterable<AbstractDto>
     */
    #[\Override]
    public function resolve(
        Request $request,
        ArgumentMetadata $argument
    ): iterable {
        $class = $argument->getType();

        if (null === $class || !is_subclass_of($class, AbstractDto::class)) {
            return [];
        }

        $this->eventDispatcher->dispatch(
            new ResolvedEvent(
                $object = $this->dtoResolver->resolve($class, $request)
            )
        );

        $object->setOptional(!empty($argument->getAttributesOfType(AllowInvalid::class)));

        yield $object;
    }
}
