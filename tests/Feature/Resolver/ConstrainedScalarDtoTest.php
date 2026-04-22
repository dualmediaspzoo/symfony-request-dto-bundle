<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ConstrainedScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ConstrainedScalarDtoTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function testAllValid(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '5',
                'boundedString' => 'hello',
                'ratio' => '0.5',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame(5, $dto->positiveInt);
        static::assertSame('hello', $dto->boundedString);
        static::assertSame(0.5, $dto->ratio);
    }

    public function testGreaterThanViolation(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '0',
                'boundedString' => 'hello',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('positiveInt', $violations);
        static::assertEquals('This value should be greater than 0.', $violations['positiveInt'][0]->getMessage());
        static::assertArrayNotHasKey('boundedString', $violations);
        static::assertArrayNotHasKey('ratio', $violations);
    }

    public function testNegativeIntViolation(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '-3',
                'boundedString' => 'hello',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('positiveInt', $violations);
        static::assertEquals('This value should be greater than 0.', $violations['positiveInt'][0]->getMessage());
    }

    public function testStringTooShort(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '1',
                'boundedString' => 'ab',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('boundedString', $violations);
        static::assertEquals('This value is too short. It should have 3 characters or more.', $violations['boundedString'][0]->getMessage());
    }

    public function testStringTooLong(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '1',
                'boundedString' => str_repeat('a', 51),
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('boundedString', $violations);
        static::assertEquals('This value is too long. It should have 50 characters or less.', $violations['boundedString'][0]->getMessage());
    }

    public function testRatioOutOfRange(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '1',
                'boundedString' => 'hello',
                'ratio' => '1.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('ratio', $violations);
        static::assertEquals('This value should be between 0 and 1.', $violations['ratio'][0]->getMessage());
    }

    public function testNotNullWhenMissing(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'boundedString' => 'hello',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('positiveInt', $violations);
        static::assertEquals('This value should not be null.', $violations['positiveInt'][0]->getMessage());
        static::assertArrayHasKey('ratio', $violations);
        static::assertEquals('This value should not be null.', $violations['ratio'][0]->getMessage());
    }

    public function testNotBlankWhenEmptyString(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '1',
                'boundedString' => '',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('boundedString', $violations);
        static::assertEquals('This value should not be blank.', $violations['boundedString'][0]->getMessage());
    }

    public function testMultipleViolationsOnSameField(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => '-5',
                'boundedString' => 'hello',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('positiveInt', $violations);
        static::assertEquals('This value should be greater than 0.', $violations['positiveInt'][0]->getMessage());
    }

    public function testTypeViolationPreventsConstraintCheck(): void
    {
        $dto = $this->service->resolve(
            ConstrainedScalarDto::class,
            new Request(request: [
                'positiveInt' => 'not-a-number',
                'boundedString' => 'hello',
                'ratio' => '0.5',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->positiveInt);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('positiveInt', $violations);

        $messages = array_map(
            static fn ($v) => $v->getMessage(),
            $violations['positiveInt']
        );
        static::assertContains('This value should be of type int.', $messages);
    }
}
