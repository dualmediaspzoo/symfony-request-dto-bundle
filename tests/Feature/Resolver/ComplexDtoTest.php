<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ComplexDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testFullValidResolution(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'some-path' => '42',
                'verySimpleDto' => [
                    'intField' => '20',
                    'stringField' => 'hello',
                    'dateTime' => '2024-01-15',
                ],
                'listOfDto' => [
                    ['intField' => '16', 'stringField' => 'a'],
                    ['intField' => '17', 'stringField' => 'b'],
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(42, $dto->someInput);

        static::assertInstanceOf(VerySimpleDto::class, $dto->verySimpleDto);
        static::assertSame(20, $dto->verySimpleDto->intField);
        static::assertSame('hello', $dto->verySimpleDto->stringField);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->verySimpleDto->dateTime);

        static::assertCount(2, $dto->listOfDto);
        static::assertSame(16, $dto->listOfDto[0]->intField);
        static::assertSame(17, $dto->listOfDto[1]->intField);
    }

    public function testNestedTypeCoercionViolationCascades(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'verySimpleDto' => [
                    'intField' => 'not-a-number',
                ],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('verySimpleDto.intField', $violations);
    }

    public function testListItemTypeViolation(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'verySimpleDto' => [
                    'intField' => '20',
                    'stringField' => 'ok',
                ],
                'listOfDto' => [
                    ['intField' => '20', 'stringField' => 'ok'],
                    ['intField' => 'bad', 'stringField' => 'ok'],
                ],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('listOfDto[1].intField', $violations);
        static::assertArrayNotHasKey('listOfDto[0].intField', $violations);
    }

    public function testPathAttributeResolution(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'some-path' => '99',
            ])
        );

        static::assertSame(99, $dto->someInput);
    }

    public function testVisitedStateAcrossHierarchy(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'some-path' => '1',
                'verySimpleDto' => [
                    'intField' => '20',
                ],
            ])
        );

        static::assertTrue($dto->visited('someInput'));
        static::assertTrue($dto->visited('verySimpleDto'));
        static::assertFalse($dto->visited('listOfDto'));

        static::assertNotNull($dto->verySimpleDto);
        static::assertTrue($dto->verySimpleDto->visited('intField'));
        static::assertFalse($dto->verySimpleDto->visited('stringField'));
    }

    public function testEmptyListResolvesToEmptyArray(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'verySimpleDto' => [
                    'intField' => '20',
                    'stringField' => 'ok',
                ],
                'listOfDto' => [],
            ])
        );

        static::assertSame([], $dto->listOfDto);
    }

    public function testMissingNestedDtoIsInstantiated(): void
    {
        $dto = $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [])
        );

        // Nested DTOs are always instantiated by the handler
        static::assertInstanceOf(VerySimpleDto::class, $dto->verySimpleDto);
        static::assertSame([], $dto->listOfDto);
    }
}
