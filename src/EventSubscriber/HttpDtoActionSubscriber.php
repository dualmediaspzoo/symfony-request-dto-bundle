<?php

namespace DualMedia\DtoRequestBundle\EventSubscriber;

use DualMedia\DtoRequestBundle\Exception\Http\DtoHttpException;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

/**
 * Throws {@link DtoHttpException}s when needed.
 */
class HttpDtoActionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => ['onControllerArguments', 5],
        ];
    }

    public function onControllerArguments(
        ControllerArgumentsEvent $event
    ): void {
        foreach ($event->getArguments() as $argument) {
            if (!($argument instanceof DtoInterface)
                || !$argument->isValid()
                || null === ($action = $argument->getHttpAction())) {
                continue;
            }

            throw new DtoHttpException(
                $argument,
                $action->getHttpStatusCode(),
                $action->getMessage() ?? '',
                null,
                $action->getHeaders()
            );
        }
    }
}
