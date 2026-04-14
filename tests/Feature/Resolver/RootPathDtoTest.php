<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\RootPathListDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\RootPathSingleDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests #[Path('')] on nested DTO properties, which should treat the
 * child DTO's fields as if they live at the parent's level (no extra
 * nesting key in the request data).
 */
#[Group('feature')]
#[Group('resolver')]
class RootPathDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testListAtRootPath(): void
    {
        $dto = $this->service->resolve(
            RootPathListDto::class,
            new Request(request: [
                ['intField' => '1', 'stringField' => 'first'],
                ['intField' => '2', 'stringField' => 'second'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->items);
        static::assertInstanceOf(ScalarDto::class, $dto->items[0]);
        static::assertSame(1, $dto->items[0]->intField);
        static::assertSame('first', $dto->items[0]->stringField);
        static::assertInstanceOf(ScalarDto::class, $dto->items[1]);
        static::assertSame(2, $dto->items[1]->intField);
        static::assertSame('second', $dto->items[1]->stringField);
    }

    public function testListAtRootPathWithInvalidEntry(): void
    {
        $dto = $this->service->resolve(
            RootPathListDto::class,
            new Request(request: [
                ['intField' => '1'],
                ['intField' => 'bad'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('[1].intField', $violations);
    }

    public function testListAtRootPathEmpty(): void
    {
        $dto = $this->service->resolve(
            RootPathListDto::class,
            new Request(request: [])
        );

        static::assertTrue($dto->isValid());
        static::assertEmpty($dto->items);
    }

    public function testSingleAtRootPath(): void
    {
        $dto = $this->service->resolve(
            RootPathSingleDto::class,
            new Request(request: [
                'intField' => '42',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNotNull($dto->child);
        static::assertSame(42, $dto->child->intField);
    }

    public function testSingleAtRootPathWithInvalidField(): void
    {
        $dto = $this->service->resolve(
            RootPathSingleDto::class,
            new Request(request: [
                'intField' => 'bad',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
    }
}
