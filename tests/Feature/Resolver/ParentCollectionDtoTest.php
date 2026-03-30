<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentCollectionDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ParentCollectionDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testReturnsDoctrineCollection(): void
    {
        $dto = $this->service->resolve(
            ParentCollectionDto::class,
            new Request(request: [
                'name' => 'parent',
                'children' => [
                    ['intField' => '1', 'stringField' => 'first'],
                    ['intField' => '2', 'stringField' => 'second'],
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent', $dto->name);
        static::assertInstanceOf(Collection::class, $dto->children);
        static::assertCount(2, $dto->children);
        static::assertInstanceOf(ScalarDto::class, $dto->children[0]);
        static::assertSame(1, $dto->children[0]->intField);
        static::assertSame('first', $dto->children[0]->stringField);
        static::assertSame(2, $dto->children[1]->intField);
        static::assertSame('second', $dto->children[1]->stringField);
    }

    public function testInvalidEntryInCollection(): void
    {
        $dto = $this->service->resolve(
            ParentCollectionDto::class,
            new Request(request: [
                'name' => 'parent',
                'children' => [
                    ['intField' => '1'],
                    ['intField' => 'bad'],
                ],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('children[1].intField', $violations);
        static::assertEquals('This value should be of type int.', $violations['children[1].intField'][0]->getMessage());
    }

    public function testEmptyCollection(): void
    {
        $dto = $this->service->resolve(
            ParentCollectionDto::class,
            new Request(request: [
                'name' => 'parent',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(Collection::class, $dto->children);
        static::assertCount(0, $dto->children);
    }

    public function testNonArrayInputViolation(): void
    {
        $dto = $this->service->resolve(
            ParentCollectionDto::class,
            new Request(request: [
                'name' => 'parent',
                'children' => 'not-an-array',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('children', $violations);
        static::assertEquals('This value should be of type array.', $violations['children'][0]->getMessage());
    }

    public function testChildParentRelationship(): void
    {
        $dto = $this->service->resolve(
            ParentCollectionDto::class,
            new Request(request: [
                'name' => 'parent',
                'children' => [
                    ['intField' => '1'],
                ],
            ])
        );

        static::assertCount(1, $dto->children);
        static::assertSame($dto, $dto->children[0]->getParentDto());
    }
}
