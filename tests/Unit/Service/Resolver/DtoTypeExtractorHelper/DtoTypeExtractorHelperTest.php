<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DualMedia\DtoRequestBundle\Attribute\Dto\Type;
use DualMedia\DtoRequestBundle\Model\Type\Dto;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\SubDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

// todo: add specific checks for only parts of items, create dummy classes
#[Group('unit')]
#[Group('service')]
#[Group('resolver')]
#[CoversClass(DtoTypeExtractorHelper::class)]
class DtoTypeExtractorHelperTest extends KernelTestCase
{
    private DtoTypeExtractorHelper $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(DtoTypeExtractorHelper::class);
    }

    public function testComplexValidation(): void
    {
        $dto = $this->service->extract(new \ReflectionClass(ComplexDto::class));

        foreach (['myInt', 'intArr', 'myString', 'model', 'dto', 'date'] as $key) {
            static::assertArrayHasKey($key, $dto);
            /** @var Property $prop */
            $prop = $dto[$key];
            static::assertEquals($dto, $prop->getParent());
            static::assertInstanceOf(Property::class, $prop);
        }

        /** @var Property $intEnum */
        $intEnum = $dto['intEnum'];
        static::assertEquals('object', $intEnum->getType());
        static::assertEquals('intEnum', $intEnum->getName());
        static::assertEquals('int', $intEnum->getSubType());
        static::assertFalse($intEnum->isCollection());

        /** @var Property $stringEnum */
        $stringEnum = $dto['stringEnum'];
        static::assertEquals('object', $stringEnum->getType());
        static::assertEquals('stringEnum', $stringEnum->getName());
        static::assertEquals('string', $stringEnum->getSubType());
        static::assertFalse($stringEnum->isCollection());

        /** @var Property $int */
        $int = $dto['myInt'];
        static::assertEquals('int', $int->getType());
        static::assertEquals('myInt', $int->getName());
        static::assertFalse($int->isCollection());

        /** @var Property $intArr */
        $intArr = $dto['intArr'];
        static::assertEquals('int', $intArr->getType());
        static::assertEquals('intArr', $intArr->getName());
        static::assertTrue($intArr->isCollection());

        /** @var Property $string */
        $string = $dto['myString'];
        static::assertEquals('string', $string->getType());
        static::assertEquals('myString', $string->getName());
        static::assertFalse($string->isCollection());

        /** @var Property $date */
        $date = $dto['date'];
        static::assertEquals('object', $date->getType());
        static::assertEquals('date', $date->getName());
        static::assertEquals('string', $date->getSubType());
        static::assertFalse($date->isCollection());

        /** @var Property $model */
        $model = $dto['model'];
        static::assertEquals('object', $model->getType());
        static::assertEquals('model', $model->getName());
        static::assertFalse($model->isCollection());
        static::assertEquals(DummyModel::class, $model->getFqcn());

        static::assertNotNull($find = $model->getFindAttribute());
        static::assertEquals([
            'id' => 'id',
            'custom' => '$customProp',
            'date' => 'whatever',
        ], $find->getFields());

        static::assertCount(2, $find->getTypes());
        static::assertInstanceOf(Type::class, $type = $find->getTypes()['id'] ?? null);

        static::assertEquals('int', $type->type);
        static::assertFalse($type->collection);
        static::assertNull($type->format);

        static::assertInstanceOf(Type::class, $date = $find->getTypes()['date'] ?? null);

        static::assertEquals('object', $date->type);
        static::assertFalse($date->collection);
        static::assertNotNull($date->format);

        // check if sub-prop was created
        static::assertArrayHasKey('id', $model);

        /** @var Property $subProp */
        $subProp = $model['id'];

        static::assertEquals('int', $subProp->getType());
        static::assertFalse($subProp->isCollection());

        /** @var Property $subDate */
        $subDate = $model['date'];

        static::assertEquals('object', $subDate->getType());
        static::assertFalse($subDate->isCollection());
        static::assertEquals('string', $subDate->getSubType());
        static::assertEquals('date', $subDate->getName());

        /** @var Dto $subDto */
        $subDto = $dto['dto'];
        static::assertEquals('object', $subDto->getType());
        static::assertEquals('dto', $subDto->getName());
        static::assertFalse($subDto->isCollection());
        static::assertEquals(SubDto::class, $subDto->getFqcn());

        foreach (['subDtoInt', 'subDtoFloat', 'subDtoBool'] as $key) {
            static::assertArrayHasKey($key, $subDto);
        }

        /** @var Property $subInt */
        $subInt = $subDto['subDtoInt'];
        static::assertEquals('int', $subInt->getType());
        static::assertEquals('subDtoInt', $subInt->getName());
        static::assertFalse($subInt->isCollection());

        /** @var Property $subFloat */
        $subFloat = $subDto['subDtoFloat'];
        static::assertEquals('float', $subFloat->getType());
        static::assertEquals('subDtoFloat', $subFloat->getName());
        static::assertFalse($subFloat->isCollection());

        $subBool = $subDto['subDtoBool'];
        static::assertEquals('bool', $subBool->getType());
        static::assertEquals('subDtoBool', $subBool->getName());
        static::assertFalse($subBool->isCollection());
    }
}
