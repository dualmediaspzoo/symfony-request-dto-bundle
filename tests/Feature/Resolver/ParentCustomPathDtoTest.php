<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentCustomPathDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ParentCustomPathDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testChildResolvedFromCustomPath(): void
    {
        $dto = $this->service->resolve(
            ParentCustomPathDto::class,
            new Request(request: [
                'name' => 'parent-name',
                'nested-child' => [
                    'intField' => '99',
                    'stringField' => 'from-custom-path',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent-name', $dto->name);
        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertSame(99, $dto->child->intField);
        static::assertSame('from-custom-path', $dto->child->stringField);
    }

    public function testDefaultPathDoesNotResolve(): void
    {
        $dto = $this->service->resolve(
            ParentCustomPathDto::class,
            new Request(request: [
                'name' => 'parent-name',
                'child' => [
                    'intField' => '99',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('parent-name', $dto->name);
        static::assertInstanceOf(ScalarDto::class, $dto->child);
        static::assertNull($dto->child->intField);
    }

    public function testChildInvalidAtCustomPath(): void
    {
        $dto = $this->service->resolve(
            ParentCustomPathDto::class,
            new Request(request: [
                'name' => 'parent-name',
                'nested-child' => [
                    'intField' => 'bad',
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->child->intField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('nested-child.intField', $violations);
    }
}
