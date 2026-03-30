<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentPathOverrideDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\PathOverrideDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ParentPathOverrideDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testChildResolvedFromCustomPath(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
                'renamed-child' => [
                    'custom-int' => '77',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertTrue($dto->visited('name'));
        static::assertTrue($dto->visited('child'));
        static::assertSame('parent', $dto->name);
        static::assertInstanceOf(PathOverrideDto::class, $dto->child);
        static::assertSame(77, $dto->child->intField);
    }

    public function testPropertyNameDoesNotResolveChild(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
                'child' => [
                    'custom-int' => '77',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(PathOverrideDto::class, $dto->child);
        static::assertNull($dto->child->intField);
    }

    public function testChildFieldPropertyNameDoesNotResolve(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
                'renamed-child' => [
                    'intField' => '77',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(PathOverrideDto::class, $dto->child);
        static::assertNull($dto->child->intField);
    }

    public function testInvalidChildValueAtCustomPaths(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
                'renamed-child' => [
                    'custom-int' => 'bad',
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->child->intField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('renamed-child.custom-int', $violations);
        static::assertEquals('This value should be of type int.', $violations['renamed-child.custom-int'][0]->getMessage());
    }

    public function testEmptyChildData(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent', $dto->name);
        static::assertInstanceOf(PathOverrideDto::class, $dto->child);
        static::assertNull($dto->child->intField);
    }

    public function testChildNotVisitedWhenInputMissing(): void
    {
        $dto = $this->service->resolve(
            ParentPathOverrideDto::class,
            new Request(request: [
                'name' => 'parent',
            ])
        );

        static::assertFalse($dto->visited('child'));
    }
}
