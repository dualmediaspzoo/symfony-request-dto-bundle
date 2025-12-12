<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\CustomProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\DefaultProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\EntityHasNoProviderException;
use DualMedia\DtoRequestBundle\Interface\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Entity\TestEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;

#[Group('unit')]
#[Group('service')]
#[Group('entity')]
#[CoversClass(EntityProviderService::class)]
class EntityProviderServiceTest extends TestCase
{
    public function testNoEntityProvider(): void
    {
        $this->expectException(EntityHasNoProviderException::class);
        $this->expectExceptionMessage(sprintf(
            'No entity provider was found for model %s',
            'invalidFqcn'
        ));

        /** @psalm-suppress InvalidArgument */
        $service = new EntityProviderService([
            'some_id' => [[
                $this->createMock(ProviderInterface::class),
                'not-the-same-fqcn',
                true,
            ]],
        ]);

        $service->getProvider('invalidFqcn');
    }

    public function testDefaultProviderNotFound(): void
    {
        $this->expectException(DefaultProviderNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Default provider not found for model %s',
            'invalidFqcn'
        ));

        /** @psalm-suppress InvalidArgument */
        $service = new EntityProviderService([
            'some_id' => [[
                $this->createMock(ProviderInterface::class),
                'invalidFqcn',
                false,
            ]],
        ]);

        $service->getProvider('invalidFqcn');
    }

    public function testCustomProviderNotFound(): void
    {
        $this->expectException(CustomProviderNotFoundException::class);
        $this->expectExceptionMessage(sprintf(
            'Custom provider with id %s not found for model %s',
            'different_service_id',
            'invalidFqcn'
        ));

        /** @psalm-suppress InvalidArgument */
        $service = new EntityProviderService([
            'some_id' => [[
                $this->createMock(ProviderInterface::class),
                'invalidFqcn',
                false,
            ]],
        ]);

        $service->getProvider('invalidFqcn', 'different_service_id');
    }

    #[TestWith(['some_id', 'whateverFQCN'])]
    #[TestWith(['different_id', '\\Class\\FQCN', 'different_id'])]
    #[TestWith(['other_id', '\\Class\\FQCN', 'other_id', false])]
    public function testGetProvider(
        string $serviceId,
        string $fqcn,
        string|null $providerId = null,
        bool $default = true
    ): void {
        $mock = $this->createMock(ProviderInterface::class);

        /** @psalm-suppress InvalidArgument */
        $service = new EntityProviderService([
            $serviceId => [[
                $mock,
                $fqcn,
                $default,
            ]],
        ]);

        static::assertEquals(
            $mock,
            $service->getProvider($fqcn, $providerId)
        );
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testHandleMissingKeyTargetProvider(
        bool $result
    ): void {
        $mock = $this->createMock(TargetProviderService::class);
        $mock->method('setFqcn')
            ->with(TestEntity::class)
            ->willReturn($result);

        $service = new EntityProviderService([], $mock);

        if ($result) {
            static::assertEquals($mock, $service->getProvider(TestEntity::class));
        } else {
            $this->expectException(EntityHasNoProviderException::class);
            $this->expectExceptionMessage(sprintf(
                'No entity provider was found for model %s',
                TestEntity::class
            ));
            $service->getProvider(TestEntity::class);
        }
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testHandleNoDefaultTargetProvider(
        bool $result
    ): void {
        $mock = $this->createMock(TargetProviderService::class);
        $mock->method('setFqcn')
            ->with(TestEntity::class)
            ->willReturn($result);

        $service = new EntityProviderService([
            'dummy_service' => [[
                $this->createMock(ProviderInterface::class),
                TestEntity::class,
                false,
            ]],
        ], $mock);

        if ($result) {
            static::assertEquals($mock, $service->getProvider(TestEntity::class));
        } else {
            $this->expectException(DefaultProviderNotFoundException::class);
            $this->expectExceptionMessage(sprintf(
                'Default provider not found for model %s',
                TestEntity::class
            ));
            $service->getProvider(TestEntity::class);
        }
    }
}
