<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentListDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ParentListDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
        static::markTestIncomplete('Collection DTO resolution is not yet implemented');
    }

    public function testListChildResolve(): void
    {
        $dto = $this->service->resolve(
            ParentListDto::class,
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
        static::assertCount(2, $dto->children);
        static::assertSame(1, $dto->children[0]->intField);
        static::assertSame('first', $dto->children[0]->stringField);
        static::assertSame(2, $dto->children[1]->intField);
        static::assertSame('second', $dto->children[1]->stringField);
    }

    public function testListChildWithInvalidEntry(): void
    {
        $dto = $this->service->resolve(
            ParentListDto::class,
            new Request(request: [
                'name' => 'parent',
                'children' => [
                    ['intField' => '1'],
                    ['intField' => 'bad'],
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertSame('parent', $dto->name);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('children.1.intField', $violations);
    }

    public function testEmptyList(): void
    {
        $dto = $this->service->resolve(
            ParentListDto::class,
            new Request(request: [
                'name' => 'parent',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent', $dto->name);
        static::assertEmpty($dto->children);
    }
}
