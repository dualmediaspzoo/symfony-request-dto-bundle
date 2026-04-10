<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DateTimeDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\NestedDateTimeDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class NestedDateTimeDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testNestedDateTimeResolution(): void
    {
        $dto = $this->resolver->resolve(
            NestedDateTimeDto::class,
            new Request(request: [
                'label' => 'test',
                'dates' => [
                    'dateField' => '2024-06-15',
                    'formattedDate' => '2024-06-15 10:30:00',
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('test', $dto->label);
        static::assertInstanceOf(DateTimeDto::class, $dto->dates);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dates->dateField);
        static::assertSame('2024-06-15', $dto->dates->dateField->format('Y-m-d'));
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dates->formattedDate);
        static::assertSame('2024-06-15 10:30:00', $dto->dates->formattedDate->format('Y-m-d H:i:s'));
    }

    public function testNestedDateCollectionInNestedDto(): void
    {
        $dto = $this->resolver->resolve(
            NestedDateTimeDto::class,
            new Request(request: [
                'dates' => [
                    'dateCollection' => ['2024-01-01', '2024-12-31'],
                ],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNotNull($dto->dates);
        static::assertCount(2, $dto->dates->dateCollection);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dates->dateCollection[0]);
        static::assertInstanceOf(\DateTimeImmutable::class, $dto->dates->dateCollection[1]);
    }

    public function testNestedInvalidDateTimeViolationPath(): void
    {
        $dto = $this->resolver->resolve(
            NestedDateTimeDto::class,
            new Request(request: [
                'dates' => [
                    'dateField' => 'not-a-date',
                ],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('dates.dateField', $violations);
    }

    public function testNestedInvalidDateInCollection(): void
    {
        $dto = $this->resolver->resolve(
            NestedDateTimeDto::class,
            new Request(request: [
                'dates' => [
                    'dateCollection' => ['2024-01-01', 'invalid'],
                ],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        // All constraint wraps per-element violations with [index]
        static::assertArrayHasKey('dates.dateCollection[1]', $violations);
    }

    public function testMissingNestedDtoIsInstantiated(): void
    {
        $dto = $this->resolver->resolve(
            NestedDateTimeDto::class,
            new Request(request: [
                'label' => 'only-label',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('only-label', $dto->label);
        // Nested DTOs are always instantiated by the handler
        static::assertInstanceOf(DateTimeDto::class, $dto->dates);
    }
}
