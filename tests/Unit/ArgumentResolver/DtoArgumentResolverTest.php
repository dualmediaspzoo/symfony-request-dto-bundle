<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\ArgumentResolver;

use DualMedia\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DualMedia\DtoRequestBundle\Event\DtoResolvedEvent;
use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto\BaseDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto\SubDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class DtoArgumentResolverTest extends KernelTestCase
{
    private ArgumentValueResolverInterface $service;
    private MockObject $eventMock;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->eventMock = $this->createMock(EventDispatcherInterface::class);
        $this->getContainer()->set('event_dispatcher', $this->eventMock);
        $this->service = $this->getService(DtoArgumentResolver::class);
    }

    public function testSupported(): void
    {
        $request = $this->createMock(Request::class);
        $mock = $this->createMock(ArgumentMetadata::class);
        $mock->method('getType')
            ->willReturn(BaseDto::class);

        $this->assertTrue(
            $this->service->supports(
                $request,
                $mock
            ),
            'Type should be supported'
        );

        $mock = $this->createMock(ArgumentMetadata::class);
        $mock->method('getType')
            ->willReturn(DtoInterface::class);

        $this->assertFalse(
            $this->service->supports(
                $request,
                $mock
            ),
            'Type should not be supported'
        );

        $mock = $this->createMock(ArgumentMetadata::class);
        $mock->method('getType')
            ->willReturn(static::class);

        $this->assertFalse(
            $this->service->supports(
                $request,
                $mock
            ),
            'Type should not be supported'
        );
    }

    public function testResolve(): void
    {
        $event = $this->deferCallable(function ($event): void {
            $this->assertInstanceOf(DtoResolvedEvent::class, $event);
            $this->assertInstanceOf(SubDto::class, $event->getDto());
        });

        $this->eventMock->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function (...$args) use ($event) {
                $event->set($args);

                return $args[0];
            });

        $request = new Request([], [
            'value' => 155,
            'floaty_boy' => 22.5,
        ]);

        $mock = $this->createMock(ArgumentMetadata::class);
        $mock->method('getType')
            ->willReturn(SubDto::class);
        $mock->method('isNullable')
            ->willReturn(false);

        /**
         * @var SubDto $dto
         *
         * @psalm-suppress InvalidArgument
         */
        $dto = iterator_to_array($this->service->resolve($request, $mock))[0];

        $this->assertInstanceOf(SubDto::class, $dto);
        $this->assertTrue($dto->isValid());
        $this->assertEquals(155, $dto->value);
        $this->assertEquals(22.5, $dto->floatVal);
        $this->assertTrue($dto->visited('value'));
        $this->assertTrue($dto->visited('floatVal'));
    }
}
