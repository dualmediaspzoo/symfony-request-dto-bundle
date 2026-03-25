<?php

namespace DualMedia\DtoRequestBundle\EventSubscriber;

use DualMedia\DtoRequestBundle\Event\DtoActionEvent;
use DualMedia\DtoRequestBundle\Event\DtoInvalidEvent;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DtoSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => ['onArgumentEvent', 5],
        ];
    }

    public function onArgumentEvent(
        ControllerArgumentsEvent $event
    ): void {
        /** @var list<DtoInterface> $arguments */
        $arguments = [];

        foreach ($event->getArguments() as $argument) {
            if (!$argument instanceof DtoInterface) {
                continue;
            }

            $arguments[] = $argument;
        }

        if (empty($arguments)) {
            return;
        }

        $request = $event->getRequest();
        $requestType = $event->getRequestType();
        /** @var list<DtoInterface> $invalid */
        $invalid = [];

        foreach ($arguments as $argument) {
            $valid = $argument->isValid();

            if ($valid && null !== ($action = $argument->getHttpAction())) {
                $output = $this->dispatcher->dispatch(new DtoActionEvent($action, $argument, $request, $requestType));

                if (null !== ($response = $output->getResponse())) {
                    $event->setController(static fn () => $response);

                    return;
                }
            }

            if ($valid || $argument->isOptional()) {
                continue;
            }

            $invalid[] = $argument;
        }

        if (empty($invalid)) {
            return;
        }

        $output = $this->dispatcher->dispatch(new DtoInvalidEvent($invalid, $request, $requestType));

        if (null !== ($response = $output->getResponse())) {
            $event->setController(static fn () => $response);
        }
    }
}
