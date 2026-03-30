<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\PathOverrideDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class PathOverrideDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testResolvesFromCustomPath(): void
    {
        $dto = $this->service->resolve(
            PathOverrideDto::class,
            new Request(request: [
                'custom-int' => '42',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(42, $dto->intField);
    }

    public function testPropertyNameDoesNotResolve(): void
    {
        $dto = $this->service->resolve(
            PathOverrideDto::class,
            new Request(request: [
                'intField' => '42',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->intField);
        static::assertFalse($dto->visited('intField'));
    }

    public function testInvalidValueAtCustomPath(): void
    {
        $dto = $this->service->resolve(
            PathOverrideDto::class,
            new Request(request: [
                'custom-int' => 'bad',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('custom-int', $violations);
        static::assertEquals('This value should be of type int.', $violations['custom-int'][0]->getMessage());
    }

    public function testVisitedWhenInputPresent(): void
    {
        $dto = $this->service->resolve(
            PathOverrideDto::class,
            new Request(request: [
                'custom-int' => '10',
            ])
        );

        static::assertTrue($dto->visited('intField'));
    }

    public function testMissingInput(): void
    {
        $dto = $this->service->resolve(
            PathOverrideDto::class,
            new Request(request: [])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->intField);
        static::assertFalse($dto->visited('intField'));
    }
}
