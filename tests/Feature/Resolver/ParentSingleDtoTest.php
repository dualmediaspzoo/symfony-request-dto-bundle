<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentSingleDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ParentSingleDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testParentAndChildResolve(): void
    {
        $dto = $this->service->resolve(
            ParentSingleDto::class,
            new Request(request: [
                'name' => 'parent-name',
                'child' => [
                    'intField' => '10',
                    'stringField' => 'hello',
                    'floatField' => '1.5',
                    'boolField' => 'true',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent-name', $dto->name);
        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertSame(10, $dto->child->intField);
        static::assertSame('hello', $dto->child->stringField);
        static::assertSame(1.5, $dto->child->floatField);
        static::assertSame(true, $dto->child->boolField);
    }

    public function testParentValidChildInvalid(): void
    {
        $dto = $this->service->resolve(
            ParentSingleDto::class,
            new Request(request: [
                'name' => 'parent-name',
                'child' => [
                    'intField' => 'bad',
                    'stringField' => 'ok',
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertSame('parent-name', $dto->name);
        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertNull($dto->child->intField);
        static::assertSame('ok', $dto->child->stringField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('child.intField', $violations);
    }

    public function testChildParentRelationship(): void
    {
        $dto = $this->service->resolve(
            ParentSingleDto::class,
            new Request(request: [
                'name' => 'root',
                'child' => [
                    'intField' => '1',
                ],
            ])
        );

        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertSame($dto, $dto->child->getParentDto());
        static::assertSame($dto, $dto->child->getHighestParentDto());
    }

    public function testEmptyChildData(): void
    {
        $dto = $this->service->resolve(
            ParentSingleDto::class,
            new Request(request: [
                'name' => 'parent-name',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent-name', $dto->name);
        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertNull($dto->child->intField);
    }
}
