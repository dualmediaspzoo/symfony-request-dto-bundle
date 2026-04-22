<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Request;

use DualMedia\DtoRequestBundle\Dto\Event\ActionEvent;
use DualMedia\DtoRequestBundle\Dto\Event\InvalidEvent;
use DualMedia\DtoRequestBundle\Tests\Fixture\Controller\RequestKernelController;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[Group('feature')]
#[Group('request')]
class KernelRequestTest extends KernelTestCase
{
    private HttpKernelInterface $httpKernel;

    private EventDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        static::bootKernel();
        $this->httpKernel = static::getService(HttpKernelInterface::class);
        $this->dispatcher = static::getService(EventDispatcherInterface::class);
    }

    public function testValidScalarDtoHitsControllerAndReturnsItsResponse(): void
    {
        $response = $this->httpKernel->handle(Request::create('/valid?name=claude'));

        static::assertSame(RequestKernelController::OK_STATUS, $response->getStatusCode());
        static::assertSame('name=claude', $response->getContent());
    }

    public function testInvalidEventFiresAndItsResponseShortCircuitsTheController(): void
    {
        $captured = null;
        $this->dispatcher->addListener(
            InvalidEvent::class,
            static function (InvalidEvent $event) use (&$captured): void {
                $captured = $event;
                $event->setResponse(new Response('invalid-handler', 422));
            }
        );

        $response = $this->httpKernel->handle(Request::create('/invalid'));

        static::assertInstanceOf(InvalidEvent::class, $captured);
        static::assertNotEmpty($captured->getObjects());
        static::assertSame(422, $response->getStatusCode());
        static::assertSame('invalid-handler', $response->getContent());
    }

    public function testActionEventFiresAndItsResponseShortCircuitsTheController(): void
    {
        $captured = null;
        $this->dispatcher->addListener(
            ActionEvent::class,
            static function (ActionEvent $event) use (&$captured): void {
                $captured = $event;
                $event->setResponse(new Response('action-handler', 418));
            }
        );

        $response = $this->httpKernel->handle(Request::create('/action'));

        static::assertInstanceOf(ActionEvent::class, $captured);
        static::assertSame(404, $captured->getAction()->statusCode);
        static::assertSame('value', $captured->getProperty());
        static::assertSame(418, $response->getStatusCode());
        static::assertSame('action-handler', $response->getContent());
        static::assertNotSame(RequestKernelController::CONTROLLER_ACTION_STATUS, $response->getStatusCode());
    }
}
