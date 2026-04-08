<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DateTimeDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class DateTimeDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testValidDateWithoutFormat(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateField' => '2020-01-15',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateField);
        static::assertSame('2020-01-15', $dto->dateField->format('Y-m-d'));
    }

    public function testValidStrtotimeString(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateField' => 'today',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateField);
        static::assertSame((new \DateTimeImmutable('today'))->format('Y-m-d'), $dto->dateField->format('Y-m-d'));
    }

    public function testInvalidDateWithoutFormat(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateField' => 'not-a-date',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->dateField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('dateField', $violations);
    }

    public function testValidDateWithFormat(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'formattedDate' => '2020-04-10 22:33:11',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->formattedDate);
        static::assertSame('2020-04-10 22:33:11', $dto->formattedDate->format('Y-m-d H:i:s'));
    }

    public function testInvalidDateWithFormat(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'formattedDate' => '2020-01-01',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->formattedDate);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('formattedDate', $violations);
        static::assertCount(1, $violations['formattedDate']);
        static::assertEquals('This value is not a valid datetime.', $violations['formattedDate'][0]->getMessage());
    }

    public function testDateCollection(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateCollection' => ['2020-01-01', '2021-06-15'],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->dateCollection);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateCollection[0]);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dateCollection[1]);
        static::assertSame('2020-01-01', $dto->dateCollection[0]->format('Y-m-d'));
        static::assertSame('2021-06-15', $dto->dateCollection[1]->format('Y-m-d'));
    }

    public function testInvalidElementInDateCollection(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateCollection' => ['2020-01-01', 'bad-date'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('dateCollection[1]', $violations);
    }

    public function testMissingFieldStaysNull(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->dateField);
        static::assertNull($dto->formattedDate);
        static::assertSame([], $dto->dateCollection);
    }

    public function testNullStringCoercesToNull(): void
    {
        $dto = $this->service->resolve(
            DateTimeDto::class,
            new Request(request: [
                'dateField' => 'null',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->dateField);
    }
}
