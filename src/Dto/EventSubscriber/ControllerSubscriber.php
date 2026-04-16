<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\EventSubscriber;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Event\DtoInvalidEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class ControllerSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            ControllerArgumentsEvent::class => ['onArguments', 5],
        ];
    }

    public function onArguments(
        ControllerArgumentsEvent $event
    ): void {
        /** @var array<int, AbstractDto> $arguments */
        $arguments = array_filter(
            $event->getArguments(),
            static fn ($o) => $o instanceof AbstractDto
        );

        if (empty($arguments)) {
            return;
        }

        $request = $event->getRequest();
        $requestType = $event->getRequestType();
        /** @var list<AbstractDto> $invalid */
        $invalid = [];

        foreach ($arguments as $argument) {
            if ($argument->isValid() || $argument->isOptional()) {
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
