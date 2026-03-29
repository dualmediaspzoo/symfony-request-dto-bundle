<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\MiniDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentMiniDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class DtoResolverTest extends KernelTestCase
{
    private DtoResolver $service;

    protected function setUp(): void
    {
        $this->service = static::getService(DtoResolver::class);
    }

    public function test(): void
    {
        $resolved = $this->service->resolve(
            MiniDto::class,
            new Request(request: [
                'intField' => 15,
            ])
        );
        static::assertEquals(15, $resolved->intField);
    }

    public function testInvalidType(): void
    {
        $resolved = $this->service->resolve(
            MiniDto::class,
            new Request(request: [
                'intField' => 'not a number',
            ])
        );

        static::assertFalse($resolved->isValid());
        static::assertNull($resolved->intField);
        $constraints = static::getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());

        static::assertNotEmpty($constraints['intField'] ?? []);
        $constraint = $constraints['intField'][0];

        static::assertEquals(
            'This value should be of type int.',
            $constraint->getMessage()
        );
        static::assertEquals(
            'not a number',
            $constraint->getInvalidValue()
        );
    }

    public function testWithChild(): void
    {
        $resolved = $this->service->resolve(
            ParentMiniDto::class,
            new Request(request: [
                'value' => 'some stuff',
                'child' => [
                    'intField' => 22,
                ],
            ])
        );
        static::assertEquals('some stuff', $resolved->value);
        static::assertEquals(22, $resolved->child->intField);
    }

    public function testWithChildError(): void
    {
        $resolved = $this->service->resolve(
            ParentMiniDto::class,
            new Request(request: [
                'value' => 'some stuff',
                'child' => [
                    'intField' => 'invalid',
                ],
            ])
        );
        static::assertFalse($resolved->isValid());
        static::assertEquals('some stuff', $resolved->value);
        static::assertNull($resolved->child->intField);

        $constraints = static::getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());
        static::assertNotEmpty($constraints['child.intField'] ?? []);
        $constraint = $constraints['child.intField'][0];

        static::assertEquals(
            'This value should be of type int.',
            $constraint->getMessage()
        );
        static::assertEquals(
            'invalid',
            $constraint->getInvalidValue()
        );
    }
}
