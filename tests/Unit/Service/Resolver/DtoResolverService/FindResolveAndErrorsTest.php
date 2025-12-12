<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\FindDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\FindWithSecondErrorDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\FindWithSomeSecondErrorDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\MultiFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\StaticDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use DualMedia\DtoRequestBundle\Tests\Service\Entity\DummyModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

#[Group('unit')]
#[Group('service')]
#[Group('resolver')]
#[CoversClass(DtoResolverService::class)]
class FindResolveAndErrorsTest extends KernelTestCase
{
    private DtoResolverService $service;
    private MockObject $provider;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->getContainer()->set(DummyModelProvider::class, $this->provider = $this->createMock(DummyModelProvider::class));
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testFindResolving(): void
    {
        $find = $this->deferCallable(function (
            array $criteria,
            array|null $orderBy = null
        ) {
            $this->assertNull($orderBy);
            $this->assertArrayHasKey('id', $criteria);
            $this->assertEquals(15, $criteria['id']);
            $this->assertArrayHasKey('date', $criteria); // different name because of conversion
            $this->assertInstanceOf(\DateTimeImmutable::class, $criteria['date']);

            $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, '2022-06-10T12:59:37+00:00');
            $this->assertEquals($date->getTimestamp(), $criteria['date']->getTimestamp());
        });

        $this->provider->expects(static::once())
            ->method('findOneBy')
            ->willReturnCallback(function (...$args) use ($find) {
                $find->set($args);

                return new DummyModel();
            });

        $request = new Request([], [
            'id' => 15,
            'whatever' => '2022-06-10T12:59:37+00:00',
        ]);

        /** @var FindDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            FindDto::class
        );

        static::assertTrue($resolved->isValid());
        static::assertTrue($resolved->visited('model'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'id'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'date'));
    }

    public function testErrors(): void
    {
        $this->provider->expects(static::never())
            ->method('findOneBy');

        $request = new Request([], [
            'id' => 'not-a-number',
            'whatever' => 15555,
        ]);

        $resolved = $this->service->resolve(
            $request,
            FindDto::class
        );

        static::assertFalse($resolved->isValid());
        static::assertTrue($resolved->visited('model'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'id'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'date'));

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());
        static::assertArrayHasKey('whatever', $mapped);
        static::assertArrayHasKey('id', $mapped);
    }

    public function testSecondValidation(): void
    {
        $this->provider->expects(static::never())
            ->method('findOneBy');

        $resolved = $this->service->resolve(
            new Request(),
            FindWithSecondErrorDto::class
        );

        static::assertFalse($resolved->isValid());
        static::assertFalse($resolved->visited('model'));

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());
        static::assertArrayHasKey('something_id', $mapped);
        static::assertEquals($mapped['something_id'][0]->getMessage(), 'This value should not be blank.');
    }

    public function testSecondValidationWithPropertyVisit(): void
    {
        $this->provider->expects(static::never())
            ->method('findOneBy');

        $resolved = $this->service->resolve(
            new Request([], [
                'something_second' => 'aaaaaa',
            ]),
            FindWithSomeSecondErrorDto::class
        );

        static::assertFalse($resolved->isValid());
        static::assertTrue($resolved->visited('model'));

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());
        static::assertArrayHasKey('something_id', $mapped);
        static::assertEquals($mapped['something_id'][0]->getMessage(), 'This value should not be blank.');
    }

    public function testForceError(): void
    {
        $this->provider->expects(static::never())
            ->method('findBy');

        $resolved = $this->service->resolve(
            new Request(),
            MultiFindDto::class
        );

        static::assertFalse($resolved->isValid());
    }

    public function testStaticData(): void
    {
        $find = $this->deferCallable(function (
            array $criteria,
            array|null $orderBy = null
        ) {
            $this->assertNull($orderBy);
            $this->assertArrayHasKey('id', $criteria);
            $this->assertEquals(15, $criteria['id']);
            $this->assertArrayHasKey('second', $criteria);
            $this->assertEquals('yeet', $criteria['second']);
            $this->assertArrayHasKey('static', $criteria);
            $this->assertEquals(1551, $criteria['static']);
        });

        $this->provider->expects(static::once())
            ->method('findOneBy')
            ->willReturnCallback(function (...$args) use ($find) {
                $find->set($args);

                return new DummyModel();
            });

        $request = new Request([], [
            'something_id' => 15,
            'something_second' => 'yeet',
        ]);

        /** @var StaticDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            StaticDto::class
        );

        static::assertTrue($resolved->isValid());
        static::assertTrue($resolved->visited('model'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'id'));
        static::assertTrue($resolved->visitedVirtualProperty('model', 'second'));
    }
}
