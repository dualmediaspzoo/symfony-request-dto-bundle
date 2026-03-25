<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\EventSubscriber;

use DualMedia\DtoRequestBundle\Event\DtoActionEvent;
use DualMedia\DtoRequestBundle\Event\DtoInvalidEvent;
use DualMedia\DtoRequestBundle\EventSubscriber\DtoSubscriber;
use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('unit')]
#[Group('event-subscriber')]
#[CoversClass(DtoSubscriber::class)]
class DtoSubscriberTest extends TestCase
{
    private EventDispatcherInterface&MockObject $dispatcher;
    private DtoSubscriber $subscriber;
    private HttpKernelInterface&Stub $kernel;

    protected function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->subscriber = new DtoSubscriber($this->dispatcher);
        $this->kernel = static::createStub(HttpKernelInterface::class);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $events = DtoSubscriber::getSubscribedEvents();

        static::assertArrayHasKey(ControllerArgumentsEvent::class, $events);
        static::assertSame(['onArgumentEvent', 5], $events[ControllerArgumentsEvent::class]);
    }

    public function testNoArguments(): void
    {
        $event = $this->createControllerArgumentsEvent([]);

        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $this->subscriber->onArgumentEvent($event);
    }

    public function testNonDtoArgumentsAreSkipped(): void
    {
        $event = $this->createControllerArgumentsEvent(['string', 123, new \stdClass()]);

        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $this->subscriber->onArgumentEvent($event);
    }

    public function testValidDtoWithActionAndResponse(): void
    {
        $action = static::createStub(HttpActionInterface::class);
        $response = new Response('action response');
        $request = new Request();

        $dto = $this->createDtoStub(valid: true, action: $action);
        $event = $this->createControllerArgumentsEvent([$dto], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(fn ($e) => $e instanceof DtoActionEvent
                && $e->getAction() === $action
                && $e->getDto() === $dto
                && $e->getRequest() === $request
                && HttpKernelInterface::SUB_REQUEST === $e->getRequestType()))
            ->willReturnCallback(function (DtoActionEvent $e) use ($response) {
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        $controller = $event->getController();
        static::assertSame($response, $controller());
    }

    public function testValidDtoWithActionNoResponseIsSkipped(): void
    {
        $action = static::createStub(HttpActionInterface::class);

        $dto = $this->createDtoStub(valid: true, action: $action);
        $event = $this->createControllerArgumentsEvent([$dto]);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::isInstanceOf(DtoActionEvent::class))
            ->willReturnArgument(0);

        $originalController = $event->getController();
        $this->subscriber->onArgumentEvent($event);

        static::assertSame($originalController, $event->getController());
    }

    public function testValidDtoWithoutActionIsSkipped(): void
    {
        $dto = $this->createDtoStub(valid: true);
        $event = $this->createControllerArgumentsEvent([$dto]);

        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $this->subscriber->onArgumentEvent($event);
    }

    public function testInvalidDtoOptionalIsSkipped(): void
    {
        $dto = $this->createDtoStub(valid: false, optional: true);
        $event = $this->createControllerArgumentsEvent([$dto]);

        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $this->subscriber->onArgumentEvent($event);
    }

    public function testInvalidDtoOptionalWithActionIsStillSkipped(): void
    {
        $action = static::createStub(HttpActionInterface::class);
        $dto = $this->createDtoStub(valid: false, optional: true, action: $action);
        $event = $this->createControllerArgumentsEvent([$dto]);

        $this->dispatcher->expects(static::never())
            ->method('dispatch');

        $this->subscriber->onArgumentEvent($event);
    }

    public function testInvalidDtoNotOptionalDispatchesInvalidEvent(): void
    {
        $response = new Response('invalid');
        $request = new Request();

        $dto = $this->createDtoStub(valid: false, optional: false);
        $event = $this->createControllerArgumentsEvent([$dto], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(
                fn ($e) => $e instanceof DtoInvalidEvent
                    && 1 === count($e->getObjects())
                    && $dto === $e->getObjects()[0]
                    && $e->getRequest() === $request
                    && HttpKernelInterface::SUB_REQUEST === $e->getRequestType()
            ))
            ->willReturnCallback(function (DtoInvalidEvent $e) use ($response) {
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        $controller = $event->getController();
        static::assertSame($response, $controller());
    }

    public function testInvalidDtoNotOptionalNoResponseContinues(): void
    {
        $dto = $this->createDtoStub(valid: false, optional: false);
        $event = $this->createControllerArgumentsEvent([$dto]);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->willReturnArgument(0);

        $originalController = $event->getController();
        $this->subscriber->onArgumentEvent($event);

        static::assertSame($originalController, $event->getController());
    }

    public function testInvalidDtoWithActionIgnoresAction(): void
    {
        $action = static::createStub(HttpActionInterface::class);
        $response = new Response('invalid');
        $request = new Request();

        $dto = $this->createDtoStub(valid: false, optional: false, action: $action);
        $event = $this->createControllerArgumentsEvent([$dto], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(
                fn ($e) => $e instanceof DtoInvalidEvent
                    && [$dto] === $e->getObjects()
                    && $e->getRequest() === $request
                    && HttpKernelInterface::SUB_REQUEST === $e->getRequestType()
            ))
            ->willReturnCallback(function (DtoInvalidEvent $e) use ($response) {
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        $controller = $event->getController();
        static::assertSame($response, $controller());
    }

    public function testMultipleInvalidDtosCombinedInSingleEvent(): void
    {
        $response = new Response('combined');
        $request = new Request();

        $dto1 = $this->createDtoStub(valid: false, optional: false);
        $dto2 = $this->createDtoStub(valid: false, optional: false);
        $event = $this->createControllerArgumentsEvent([$dto1, $dto2], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(
                fn ($e) => $e instanceof DtoInvalidEvent
                    && 2 === count($e->getObjects())
                    && $dto1 === $e->getObjects()[0]
                    && $dto2 === $e->getObjects()[1]
                    && $e->getRequest() === $request
                    && HttpKernelInterface::SUB_REQUEST === $e->getRequestType()
            ))
            ->willReturnCallback(function (DtoInvalidEvent $e) use ($response) {
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        $controller = $event->getController();
        static::assertSame($response, $controller());
    }

    public function testOptionalDtosExcludedFromInvalidEvent(): void
    {
        $response = new Response('invalid');
        $request = new Request();

        $optionalDto = $this->createDtoStub(valid: false, optional: true);
        $requiredDto = $this->createDtoStub(valid: false, optional: false);
        $event = $this->createControllerArgumentsEvent([$optionalDto, $requiredDto], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->with(static::callback(
                fn ($e) => $e instanceof DtoInvalidEvent
                    && 1 === count($e->getObjects())
                    && $requiredDto === $e->getObjects()[0]
            ))
            ->willReturnCallback(function (DtoInvalidEvent $e) use ($response) {
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        $controller = $event->getController();
        static::assertSame($response, $controller());
    }

    public function testStopsAtFirstResponseFromAction(): void
    {
        $action = static::createStub(HttpActionInterface::class);
        $response = new Response('first');
        $request = new Request();

        $dto1 = $this->createDtoStub(valid: true, action: $action);
        $dto2 = $this->createDtoStub(valid: true, action: $action);
        $event = $this->createControllerArgumentsEvent([$dto1, $dto2], $request, HttpKernelInterface::SUB_REQUEST);

        $this->dispatcher->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(function (DtoActionEvent $e) use ($response, $request) {
                static::assertSame($request, $e->getRequest());
                static::assertSame(HttpKernelInterface::SUB_REQUEST, $e->getRequestType());
                $e->setResponse($response);

                return $e;
            });

        $this->subscriber->onArgumentEvent($event);

        static::assertSame($response, ($event->getController())());
    }

    private function createDtoStub(
        bool $valid = false,
        bool $optional = false,
        HttpActionInterface|null $action = null
    ): DtoInterface&Stub {
        $dto = static::createStub(DtoInterface::class);
        $dto->method('isValid')->willReturn($valid);
        $dto->method('isOptional')->willReturn($optional);
        $dto->method('getHttpAction')->willReturn($action);

        return $dto;
    }

    /**
     * @param list<mixed> $arguments
     */
    private function createControllerArgumentsEvent(
        array $arguments,
        Request|null $request = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): ControllerArgumentsEvent {
        return new ControllerArgumentsEvent(
            $this->kernel,
            'print_r',
            $arguments,
            $request ?? new Request(),
            $requestType
        );
    }
}
