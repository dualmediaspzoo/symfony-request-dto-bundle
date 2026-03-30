<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection;

use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
class CacheReflectorTest extends KernelTestCase
{
    private CacheReflector $service;

    protected function setUp(): void
    {
        $this->service = static::getService(CacheReflector::class);
    }

    public function testReturnsMainDto(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertInstanceOf(MainDto::class, $result);
        static::assertCount(3, $result->fields);
    }

    public function testPropertyMetadata(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertArrayHasKey('someInput', $result->fields);
        static::assertInstanceOf(Property::class, $someInput = $result->fields['someInput']);
        static::assertEquals('someInput', $someInput->name);
        static::assertEquals('some-path', $someInput->path);
        static::assertEquals(IntegerCoercer::class, $someInput->coercer);
        static::assertEquals('int', $someInput->type->type);
        static::assertFalse($someInput->type->isCollection());
    }

    public function testDtoMetadata(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertArrayHasKey('verySimpleDto', $result->fields);
        static::assertInstanceOf(Dto::class, $verySimpleDto = $result->fields['verySimpleDto']);
        static::assertEquals('verySimpleDto', $verySimpleDto->name);
        static::assertEquals('object', $verySimpleDto->type->type);
        static::assertEquals(VerySimpleDto::class, $verySimpleDto->type->fqcn);
        static::assertFalse($verySimpleDto->type->isCollection());
        static::assertNotEmpty($verySimpleDto->constraints);
    }

    public function testCollectionMetadata(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertArrayHasKey('listOfDto', $result->fields);
        static::assertInstanceOf(Property::class, $listOfDto = $result->fields['listOfDto']);
        static::assertEquals('listOfDto', $listOfDto->name);
        static::assertTrue($listOfDto->type->isCollection());
        static::assertEquals('array', $listOfDto->type->collection);
        static::assertEquals(VerySimpleDto::class, $listOfDto->type->fqcn);
    }

    public function testUnknownClassReturnsNull(): void
    {
        /** @var class-string<\DualMedia\DtoRequestBundle\Dto\AbstractDto> $class */
        $class = 'NonExistent\\Dto\\Class';

        static::assertNull($this->service->get($class));
    }
}
