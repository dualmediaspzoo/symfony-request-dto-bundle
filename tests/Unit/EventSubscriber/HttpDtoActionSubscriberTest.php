<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\EventSubscriber;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\EventSubscriber\HttpDtoActionSubscriber;
use DualMedia\DtoRequestBundle\Exception\Http\DtoHttpException;
use DualMedia\DtoRequestBundle\Interfaces\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class HttpDtoActionSubscriberTest extends KernelTestCase
{
    private HttpDtoActionSubscriber $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(HttpDtoActionSubscriber::class);
    }

    /**
     * @dataProvider provideHandle
     */
    public function testHandle(
        bool $hasDto,
        bool $valid = false,
        HttpActionInterface|null $action = null
    ): void {
        $params = [];

        if ($hasDto) {
            $mock = $this->createMock(DtoInterface::class);
            $mock->method('isValid')
                ->willReturn($valid);

            $mock->method('getHttpAction')
                ->willReturn($action);

            $params[] = $mock;
        }

        $event = new ControllerArgumentsEvent(
            self::$kernel,
            'print_r',
            $params,
            $this->createMock(Request::class),
            HttpKernelInterface::MASTER_REQUEST
        );

        $exception = null;

        try {
            $this->service->onControllerArguments($event);
        } catch (DtoHttpException $e) {
            $exception = $e;
        } finally {
            if ($valid && null !== $action) {
                $this->assertInstanceOf(DtoHttpException::class, $exception);
                $this->assertEquals($action->getHttpStatusCode(), $exception->getStatusCode());
                $this->assertEquals($action->getMessage(), $exception->getMessage());
                $this->assertEquals($action->getHeaders(), $exception->getHeaders());
            } else {
                $this->assertNull($exception);
            }
        }
    }

    public function provideHandle(): array
    {
        return [
            [false],
            [true, false],
            [true, false, new OnNull(Response::HTTP_BAD_REQUEST)],
            [true, true, new OnNull(Response::HTTP_CONFLICT, 'Some message', ['Content-Type' => 'Fake/Something'])],
            [true, true, new OnNull(Response::HTTP_ACCEPTED, null, ['Content-Type' => 'Fake/Something'])],
        ];
    }
}
