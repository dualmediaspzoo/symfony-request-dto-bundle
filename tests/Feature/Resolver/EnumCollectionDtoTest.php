<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\EnumCollectionDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class EnumCollectionDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testStringBackedEnumCollection(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'stringEnums' => ['foo', 'bar'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->stringEnums);
        static::assertSame(StringBackedEnum::Foo, $dto->stringEnums[0]);
        static::assertSame(StringBackedEnum::Bar, $dto->stringEnums[1]);
    }

    public function testIntBackedEnumCollection(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'intEnums' => ['1', '2'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->intEnums);
        static::assertSame(IntBackedEnum::One, $dto->intEnums[0]);
        static::assertSame(IntBackedEnum::Two, $dto->intEnums[1]);
    }

    public function testPureEnumCollectionByKey(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'pureEnumsByKey' => ['Alpha', 'Beta'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->pureEnumsByKey);
        static::assertSame(PureEnum::Alpha, $dto->pureEnumsByKey[0]);
        static::assertSame(PureEnum::Beta, $dto->pureEnumsByKey[1]);
    }

    public function testInvalidEnumInCollection(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'stringEnums' => ['foo', 'invalid'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        // All constraint wraps per-element violations with [index]
        static::assertArrayHasKey('stringEnums[1]', $violations);
    }

    public function testEmptyCollectionResolvesEmpty(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'stringEnums' => [],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([], $dto->stringEnums);
    }

    public function testMixedCollections(): void
    {
        $dto = $this->resolver->resolve(
            EnumCollectionDto::class,
            new Request(request: [
                'stringEnums' => ['foo'],
                'intEnums' => ['1'],
                'pureEnumsByKey' => ['Alpha'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(StringBackedEnum::Foo, $dto->stringEnums[0]);
        static::assertSame(IntBackedEnum::One, $dto->intEnums[0]);
        static::assertSame(PureEnum::Alpha, $dto->pureEnumsByKey[0]);
    }
}
