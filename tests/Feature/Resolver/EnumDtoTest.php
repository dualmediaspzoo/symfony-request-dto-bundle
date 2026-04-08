<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\EnumDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class EnumDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testIntBackedEnumByValue(
        bool $asString
    ): void {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'intEnum' => $asString ? '1' : 1,
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(IntBackedEnum::One, $dto->intEnum);
    }

    public function testIntBackedEnumByKey(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'intEnumByKey' => 'One',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(IntBackedEnum::One, $dto->intEnumByKey);
    }

    public function testPureEnumByKey(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'pureEnumByKey' => 'Alpha',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(PureEnum::Alpha, $dto->pureEnumByKey);
    }

    public function testPureEnumWithoutFromKeyFails(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'pureEnumInvalid' => 'Alpha',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->pureEnumInvalid);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('pureEnumInvalid', $violations);
    }

    public function testStringBackedEnumByValue(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'stringEnum' => 'foo',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(StringBackedEnum::Foo, $dto->stringEnum);
    }

    public function testStringBackedEnumByKey(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'stringEnumByKey' => 'Foo',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(StringBackedEnum::Foo, $dto->stringEnumByKey);
    }

    public function testInvalidBackedEnumValue(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'intEnum' => '999',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intEnum);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intEnum', $violations);
    }

    public function testInvalidEnumKey(): void
    {
        $dto = $this->service->resolve(
            EnumDto::class,
            new Request(request: [
                'intEnumByKey' => 'NonExistent',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intEnumByKey);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intEnumByKey', $violations);
    }
}
