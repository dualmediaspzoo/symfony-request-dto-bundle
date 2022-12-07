<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Resolver;

use DM\DtoRequestBundle\Exception\Dynamic\ParameterNotSupportedException;
use DM\DtoRequestBundle\Interfaces\Dynamic\ResolverInterface;
use DM\DtoRequestBundle\Service\Resolver\DynamicResolverService;
use DM\DtoRequestBundle\Tests\Model\ArrayIterator;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class DynamicResolverServiceTest extends TestCase
{
    private MockObject $mock;
    private DynamicResolverService $service;

    protected function setUp(): void
    {
        $this->mock = $this->createMock(ResolverInterface::class);

        /** @psalm-suppress InvalidArgument */
        $this->service = new DynamicResolverService(new ArrayIterator([$this->mock]));
    }

    public function testNothingSupported(): void
    {
        $this->mock->expects($this->once())
            ->method('getSupportedParameters')
            ->willReturn([]);

        $this->assertEmpty($this->service->getSupportedParameters());
    }

    public function testDuplicatedSupported(): void
    {
        $this->mock->expects($this->once())
            ->method('getSupportedParameters')
            ->willReturn(['something', 'whatever', 'something']);

        $this->assertEquals([
            'something',
            'whatever',
        ], $this->service->getSupportedParameters());
    }

    public function testNotSupported(): void
    {
        $this->mock->expects($this->once())
            ->method('getSupportedParameters')
            ->willReturn(['something']);

        $this->expectException(ParameterNotSupportedException::class);
        $this->expectExceptionMessage('Parameter other is not supported by any of the provided resolvers');
        $this->service->resolveParameter('other');
    }

    public function testSupported(): void
    {
        $this->mock->expects($this->once())
            ->method('getSupportedParameters')
            ->willReturn(['something']);

        $nameDefer = $this->deferCallable(function (string $name) {
            $this->assertEquals('something', $name);
        });

        $this->mock->expects($this->once())
            ->method('resolveParameter')
            ->willReturnCallback(function (...$args) use ($nameDefer) {
                $nameDefer->set($args);

                return 15;
            });

        $this->assertEquals(
            15,
            $this->service->resolveParameter('something')
        );
    }
}
