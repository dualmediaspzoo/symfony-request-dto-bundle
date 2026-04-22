<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\EnumAttributesDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\IntBackedEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\MultiWordEnum;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\StringBackedEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class EnumAttributesDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testAllowedEnumAcceptsListedCase(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'intRestricted' => 1,
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(IntBackedEnum::One, $dto->intRestricted);
    }

    public function testAllowedEnumRejectsUnlistedCase(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'intRestricted' => 2,
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intRestricted);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intRestricted', $violations);
    }

    public function testAllowedEnumStringBackedAcceptsListedCase(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'stringRestricted' => 'foo',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(StringBackedEnum::Foo, $dto->stringRestricted);
    }

    public function testAllowedEnumStringBackedRejectsUnlistedCase(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'stringRestricted' => 'bar',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->stringRestricted);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('stringRestricted', $violations);
    }

    public function testLabelProcessorResolvesPascalCaseKey(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWord' => 'FIRST_CASE',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(MultiWordEnum::FirstCase, $dto->multiWord);
    }

    public function testLabelProcessorResolvesEachCase(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWord' => 'SECOND_CASE',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(MultiWordEnum::SecondCase, $dto->multiWord);
    }

    public function testLabelProcessorRejectsRawCaseName(): void
    {
        // Without the processor, "FirstCase" would match directly. With the
        // PascalCase processor enabled, the input must arrive in UPPER_SNAKE_CASE.
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWord' => 'FirstCase',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->multiWord);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('multiWord', $violations);
    }

    public function testLabelProcessorRejectsUnknownKey(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWord' => 'NOT_A_CASE',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->multiWord);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('multiWord', $violations);
    }

    public function testLabelProcessorWithAllowedEnumAcceptsListed(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWordRestricted' => 'FIRST_CASE',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(MultiWordEnum::FirstCase, $dto->multiWordRestricted);
    }

    public function testLabelProcessorWithAllowedEnumRejectsValidKeyOutsideAllowed(): void
    {
        // SECOND_CASE is a real enum case but not in the WithAllowedEnum list.
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'multiWordRestricted' => 'SECOND_CASE',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->multiWordRestricted);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('multiWordRestricted', $violations);
    }

    public function testAllowedEnumOnCollectionAcceptsListedValues(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'intCollectionRestricted' => [2, 2],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([IntBackedEnum::Two, IntBackedEnum::Two], $dto->intCollectionRestricted);
    }

    public function testAllowedEnumOnCollectionRejectsUnlistedValues(): void
    {
        $dto = $this->service->resolve(
            EnumAttributesDto::class,
            new Request(request: [
                'intCollectionRestricted' => [1, 2],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertNotEmpty(array_filter(
            array_keys($violations),
            static fn (string $path) => str_starts_with($path, 'intCollectionRestricted')
        ));
    }
}
