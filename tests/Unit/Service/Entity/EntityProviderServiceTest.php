<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity;

use DualMedia\DtoRequestBundle\Exception\Entity\CustomProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\DefaultProviderNotFoundException;
use DualMedia\DtoRequestBundle\Exception\Entity\EntityHasNoProviderException;
use DualMedia\DtoRequestBundle\Interfaces\Entity\ProviderInterface;
use DualMedia\DtoRequestBundle\Service\Entity\EntityProviderService;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;

class EntityProviderServiceTest extends TestCase
{
    public function testNoEntityProvider(): void
    {
        $this->expectException(EntityHasNoProviderException::class);
        $this->expectExceptionMessage(sprintf(
            "No entity provider was found for model %s",
            "invalidFqcn"
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
            "Default provider not found for model %s",
            "invalidFqcn"
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
            "Custom provider with id %s not found for model %s",
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

    /**
     * @testWith ["some_id", "whateverFQCN"]
     *           ["different_id", "\\Class\\FQCN", "different_id"]
     *           ["other_id", "\\Class\\FQCN", "other_id", false]
     */
    public function testGetProvider(
        string $serviceId,
        string $fqcn,
        ?string $providerId = null,
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

        $this->assertEquals(
            $mock,
            $service->getProvider($fqcn, $providerId)
        );
    }
}
