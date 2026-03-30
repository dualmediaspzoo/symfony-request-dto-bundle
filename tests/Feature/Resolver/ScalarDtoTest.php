<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ScalarDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testAllValidValues(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => '42',
                'stringField' => 'hello',
                'floatField' => '3.14',
                'boolField' => '1',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(42, $dto->intField);
        static::assertSame('hello', $dto->stringField);
        static::assertSame(3.14, $dto->floatField);
        static::assertSame(true, $dto->boolField);
    }

    public function testMissingFieldsStayNull(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->intField);
        static::assertNull($dto->stringField);
        static::assertNull($dto->floatField);
        static::assertNull($dto->boolField);
    }

    public function testPartialFields(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => '10',
                'boolField' => 'false',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(10, $dto->intField);
        static::assertNull($dto->stringField);
        static::assertNull($dto->floatField);
        static::assertSame(false, $dto->boolField);
    }

    public function testInvalidInt(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => 'not-a-number',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
        static::assertEquals('This value should be of type int.', $violations['intField'][0]->getMessage());
        static::assertEquals('not-a-number', $violations['intField'][0]->getInvalidValue());
    }

    public function testInvalidFloat(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'floatField' => 'abc',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->floatField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('floatField', $violations);
        static::assertEquals('This value should be of type float.', $violations['floatField'][0]->getMessage());
    }

    public function testInvalidBool(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'boolField' => 'not-a-bool',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->boolField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('boolField', $violations);
        static::assertEquals('This value should be of type bool.', $violations['boolField'][0]->getMessage());
    }

    public function testMultipleInvalidFields(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => 'bad',
                'floatField' => 'bad',
                'boolField' => 'bad',
                'stringField' => 'valid',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->intField);
        static::assertNull($dto->floatField);
        static::assertNull($dto->boolField);
        static::assertSame('valid', $dto->stringField);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('intField', $violations);
        static::assertArrayHasKey('floatField', $violations);
        static::assertArrayHasKey('boolField', $violations);
        static::assertArrayNotHasKey('stringField', $violations);
    }

    public function testBoolCoercionVariants(): void
    {
        $variants = [
            ['1', true],
            ['0', false],
            ['true', true],
            ['false', false],
        ];

        foreach ($variants as [$input, $expected]) {
            $dto = $this->service->resolve(
                ScalarDto::class,
                new Request(request: ['boolField' => $input])
            );

            static::assertTrue($dto->isValid(), "Failed for input: $input");
            static::assertSame($expected, $dto->boolField, "Failed for input: $input");
        }
    }

    public function testVisitedOnlyWhenInputPresent(): void
    {
        $dto = $this->service->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => '5',
            ])
        );

        static::assertTrue($dto->visited('intField'));
        static::assertFalse($dto->visited('stringField'));
        static::assertFalse($dto->visited('floatField'));
        static::assertFalse($dto->visited('boolField'));
    }
}
