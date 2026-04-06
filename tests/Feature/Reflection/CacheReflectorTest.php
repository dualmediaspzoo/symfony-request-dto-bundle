<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection;

use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\VerySimpleDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\TypeIdentifier;

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
        static::assertTrue($someInput->type->isIdentifiedBy(TypeIdentifier::INT));
        static::assertFalse(TypeInfoUtils::isCollection($someInput->type));
    }

    public function testDtoMetadata(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertArrayHasKey('verySimpleDto', $result->fields);
        static::assertInstanceOf(Dto::class, $verySimpleDto = $result->fields['verySimpleDto']);
        static::assertEquals('verySimpleDto', $verySimpleDto->name);
        static::assertEquals(VerySimpleDto::class, TypeInfoUtils::getClassName($verySimpleDto->type));
        static::assertFalse(TypeInfoUtils::isCollection($verySimpleDto->type));
        static::assertNotEmpty($verySimpleDto->constraints);
    }

    public function testCollectionMetadata(): void
    {
        $result = $this->service->get(ComplexDto::class);

        static::assertArrayHasKey('listOfDto', $result->fields);
        // listOfDto is a DTO collection so it becomes a Dto metadata entry
        static::assertInstanceOf(Dto::class, $listOfDto = $result->fields['listOfDto']);
        static::assertEquals('listOfDto', $listOfDto->name);
        static::assertInstanceOf(CollectionType::class, $listOfDto->type);
        static::assertEquals(VerySimpleDto::class, TypeInfoUtils::getCollectionValueClassName($listOfDto->type));
    }

    public function testUnknownClassReturnsNull(): void
    {
        /** @var class-string<\DualMedia\DtoRequestBundle\Dto\AbstractDto> $class */
        $class = 'NonExistent\\Dto\\Class';

        static::assertNull($this->service->get($class));
    }
}
