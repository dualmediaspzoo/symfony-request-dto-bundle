<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\Provider\EntityProvider;
use DualMedia\DtoRequestBundle\Provider\EntityProviderRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[CoversClass(EntityProviderRegistry::class)]
#[Group('unit')]
#[Group('provider')]
class EntityProviderRegistryTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EntityProviderRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createRealMockedServiceInstance(EntityProviderRegistry::class);
    }

    public function testGetCreatesProvider(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $this->getMockedService(ManagerRegistry::class)
            ->expects(static::once())
            ->method('getRepository')
            ->with(\stdClass::class)
            ->willReturn($repository);

        $provider = $this->registry->get(\stdClass::class);
        static::assertInstanceOf(EntityProvider::class, $provider);
    }

    public function testGetCachesProvider(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $this->getMockedService(ManagerRegistry::class)
            ->expects(static::once())
            ->method('getRepository')
            ->with(\stdClass::class)
            ->willReturn($repository);

        $first = $this->registry->get(\stdClass::class);
        $second = $this->registry->get(\stdClass::class);
        static::assertSame($first, $second);
    }

    public function testGetDifferentClassesDifferentProviders(): void
    {
        $repo1 = $this->createMock(EntityRepository::class);
        $repo2 = $this->createMock(EntityRepository::class);

        $this->getMockedService(ManagerRegistry::class)
            ->method('getRepository')
            ->willReturnCallback(static fn (string $class) => match ($class) {
                \stdClass::class => $repo1,
                \ArrayObject::class => $repo2,
            });

        $first = $this->registry->get(\stdClass::class);
        $second = $this->registry->get(\ArrayObject::class);
        static::assertNotSame($first, $second);
    }
}
