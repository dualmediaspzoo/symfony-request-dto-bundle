<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class VerySimpleDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testAllValidValues(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '20',
                'stringField' => 'hello',
                'dateTime' => '2024-06-15',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(20, $dto->intField);
        static::assertSame('hello', $dto->stringField);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateTime);
    }

    public function testIntFieldNotNullConstraint(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'stringField' => 'hello',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
    }

    public function testIntFieldGreaterThanConstraint(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '5',
                'stringField' => 'hello',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
    }

    public function testStringFieldNotBlankConstraint(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '20',
                'stringField' => '',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('stringField', $violations);
    }

    public function testDateTimeCoercion(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '20',
                'stringField' => 'ok',
                'dateTime' => '2024-01-15T10:30:00+00:00',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateTime);
        static::assertSame('2024-01-15', $dto->dateTime->format('Y-m-d'));
    }

    public function testInvalidDateTimeType(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '20',
                'stringField' => 'ok',
                'dateTime' => 'not-a-date',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('dateTime', $violations);
    }

    public function testMultipleConstraintViolations(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '5',
                'stringField' => '',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
        static::assertArrayHasKey('stringField', $violations);
    }

    public function testMissingDateTimeStaysNull(): void
    {
        $dto = $this->resolver->resolve(
            VerySimpleDto::class,
            new Request(request: [
                'intField' => '20',
                'stringField' => 'ok',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->dateTime);
    }
}
