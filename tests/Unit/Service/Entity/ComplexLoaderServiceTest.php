<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Entity;

use DM\DtoRequestBundle\Exception\Entity\ComplexLoaderFunctionNotFoundException;
use DM\DtoRequestBundle\Exception\Entity\ComplexLoaderNotFoundException;
use DM\DtoRequestBundle\Interfaces\Attribute\FindComplexInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ComplexLoaderInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DM\DtoRequestBundle\Interfaces\Entity\ProviderServiceInterface;
use DM\DtoRequestBundle\Service\Entity\ComplexLoaderService;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ComplexLoaderServiceTest extends TestCase
{
    /**
     * @var MockObject|ProviderServiceInterface
     */
    private MockObject $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ProviderServiceInterface::class);
    }

    public function testNotFoundLoader(): void
    {
        $mock = $this->createMockWithMethods(ComplexLoaderInterface::class, ['custom_call']);
        $mock->expects($this->never())
            ->method('custom_call');

        /** @psalm-suppress InvalidArgument */
        $service = new ComplexLoaderService([
            'some_id' => $mock,
        ], $this->provider);

        $this->expectException(ComplexLoaderNotFoundException::class);
        $find = $this->createMock(FindComplexInterface::class);
        $find->expects($this->exactly(2))
            ->method('getService')
            ->willReturn('some_other_id');

        $service->loadComplex('does_not_matter', $find, []);
    }

    public function testMethodNotExists(): void
    {
        $mock = $this->createMockWithMethods(ComplexLoaderInterface::class, ['custom_call']);
        $mock->expects($this->never())
            ->method('custom_call');

        /** @psalm-suppress InvalidArgument */
        $service = new ComplexLoaderService([
            'some_id' => $mock,
        ], $this->provider);

        $this->expectException(ComplexLoaderFunctionNotFoundException::class);
        $find = $this->createMock(FindComplexInterface::class);
        $find->expects($this->exactly(3))
            ->method('getService')
            ->willReturn('some_id');

        $find->expects($this->exactly(2))
            ->method('getFn')
            ->willReturn('unknown_call');

        $service->loadComplex('does_not_matter', $find, []);
    }

    /**
     * @testWith ["something", "some_id", "my_custom_call", 15, {"aa": 15}]
     *           ["other", "other_id", "fn_whatever", null, {"test": 15, "aaaa": 55.5}, "custom", {"something":"DESC"}]
     *           ["\\Custom\\Class", "\\Custom\\ServiceId", "loadEntity", {}, {"something": "here"}, "specified"]
     */
    public function testLoadComplex(
        string $fqcn,
        string $serviceId,
        string $fn,
        $output,
        array $input,
        ?string $providerId = null,
        ?array $orderBy = null
    ): void {
        // this test does not mock actually calling these objects
        $loader = $this->createMockWithMethods(ComplexLoaderInterface::class, [$fn]);
        $loader->expects($this->never())
            ->method($fn);

        /** @psalm-suppress InvalidArgument */
        $service = new ComplexLoaderService([
            $serviceId => $loader,
        ], $this->provider);

        $find = $this->createMock(FindComplexInterface::class);
        $find->expects($this->exactly(3))
            ->method('getService')
            ->willReturn($serviceId);

        $find->expects($this->exactly(2))
            ->method('getFn')
            ->willReturn($fn);

        $find->expects($this->once())
            ->method('getProviderId')
            ->willReturn($providerId);

        $find->expects($this->once())
            ->method('getOrderBy')
            ->willReturn($orderBy);

        $providerMock = $this->createMock(ProviderInterface::class);
        $getProviderCheck = $this->deferCallable(function (string $in, ?string $provIdIn) use ($fqcn, $providerId) {
            $this->assertEquals($fqcn, $in);
            $this->assertEquals($providerId, $provIdIn);
        });

        $this->provider->expects($this->once())
            ->method('getProvider')
            ->willReturnCallback(function (...$args) use ($getProviderCheck, $providerMock) {
                $getProviderCheck->set($args);

                return $providerMock;
            });

        $findComplexCheck = $this->deferCallable(function (callable $c, array $in, ?array $ordIn) use ($input, $orderBy) {
            $this->assertEquals($input, $in);
            $this->assertEquals($orderBy, $ordIn);
        });

        $providerMock->expects($this->once())
            ->method('findComplex')
            ->willReturnCallback(function (...$args) use ($findComplexCheck, $output) {
                $findComplexCheck->set($args);

                return $output;
            });

        $this->assertEquals(
            $output,
            $service->loadComplex($fqcn, $find, $input)
        );
    }
}
