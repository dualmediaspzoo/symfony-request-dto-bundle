<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\WithObjectProviderDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestObjectProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class WithObjectProviderDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private TestObjectProvider $provider;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->provider = static::getService(TestObjectProvider::class);
        $this->provider->store = [];
        $this->provider->calls = [];
    }

    public function testProviderClosureIsInvokedAndReturnsObject(): void
    {
        $expected = new \stdClass();
        $expected->id = '42';
        $this->provider->store['42'] = $expected;

        $dto = $this->resolver->resolve(
            WithObjectProviderDto::class,
            new Request(request: ['inputId' => '42'])
        );

        static::assertTrue($dto->isValid());
        static::assertSame($expected, $dto->thing);
        static::assertCount(1, $this->provider->calls);
        static::assertSame('42', (string)$this->provider->calls[0]['criteria']['id']);
    }

    public function testProviderClosureReturnsNullWhenNotFound(): void
    {
        $dto = $this->resolver->resolve(
            WithObjectProviderDto::class,
            new Request(request: ['inputId' => '99'])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->thing);
        static::assertCount(1, $this->provider->calls);
    }

    public function testMetadataCarriesResolvedServiceId(): void
    {
        $reflector = static::getService(\DualMedia\DtoRequestBundle\Reflection\Reflector::class);
        $mainDto = $reflector->reflect(WithObjectProviderDto::class);

        $property = $mainDto->fields['thing'];
        static::assertInstanceOf(\DualMedia\DtoRequestBundle\Metadata\Model\Property::class, $property);
        static::assertSame(TestObjectProvider::class, $property->objectProviderServiceId);
    }

    public function testCachedMetadataPreservesServiceIdAfterClosureStrip(): void
    {
        $reflector = static::getService(\DualMedia\DtoRequestBundle\Reflection\Reflector::class);
        $helper = static::getService(\DualMedia\DtoRequestBundle\Reflection\RuntimeResolve::class);

        $prepared = $helper->prepareForCache($reflector->reflect(WithObjectProviderDto::class));

        $property = $prepared->fields['thing'];
        static::assertInstanceOf(\DualMedia\DtoRequestBundle\Metadata\Model\Property::class, $property);
        static::assertSame(TestObjectProvider::class, $property->objectProviderServiceId);
        static::assertTrue($property->requiresRuntimeResolve);
        // closure stripped from meta
        static::assertSame([], array_filter(
            $property->meta,
            static fn (object $m): bool => $m instanceof \DualMedia\DtoRequestBundle\Metadata\Model\WithObjectProvider
        ));

        $restored = $helper->restoreRuntimeConstraints(WithObjectProviderDto::class, $prepared);
        $restoredProperty = $restored->fields['thing'];
        static::assertInstanceOf(\DualMedia\DtoRequestBundle\Metadata\Model\Property::class, $restoredProperty);
        static::assertCount(1, array_filter(
            $restoredProperty->meta,
            static fn (object $m): bool => $m instanceof \DualMedia\DtoRequestBundle\Metadata\Model\WithObjectProvider
        ));
    }
}
