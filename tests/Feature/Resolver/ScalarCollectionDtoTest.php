<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarCollectionDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ScalarCollectionDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testValidIntArray(): void
    {
        $dto = $this->service->resolve(
            ScalarCollectionDto::class,
            new Request(request: [
                'ids' => ['1', '2', '3'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([1, 2, 3], $dto->ids);
    }

    public function testValidStringArray(): void
    {
        $dto = $this->service->resolve(
            ScalarCollectionDto::class,
            new Request(request: [
                'tags' => ['foo', 'bar'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(['foo', 'bar'], $dto->tags);
    }

    public function testEmptyArrays(): void
    {
        $dto = $this->service->resolve(
            ScalarCollectionDto::class,
            new Request(request: [])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([], $dto->ids);
        static::assertSame([], $dto->tags);
    }

    public function testInvalidElementInIntArray(): void
    {
        $dto = $this->service->resolve(
            ScalarCollectionDto::class,
            new Request(request: [
                'ids' => ['1', 'bad', '3'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('ids[1]', $violations);
        static::assertEquals('This value should be of type int.', $violations['ids[1]'][0]->getMessage());
    }

    public function testMixedValidAndInvalid(): void
    {
        $dto = $this->service->resolve(
            ScalarCollectionDto::class,
            new Request(request: [
                'ids' => ['1', '2'],
                'tags' => ['valid'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([1, 2], $dto->ids);
        static::assertSame(['valid'], $dto->tags);
    }
}
