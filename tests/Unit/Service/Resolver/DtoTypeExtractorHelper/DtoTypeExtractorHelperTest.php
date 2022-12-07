<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DM\DtoRequestBundle\Annotations\Dto\Type;
use DM\DtoRequestBundle\Model\Type\Dto;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\ComplexDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\SubDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\DummyModel;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;

// todo: add specific checks for only parts of items, create dummy classes
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
            $this->assertArrayHasKey($key, $dto);
            /** @var Property $prop */
            $prop = $dto[$key];
            $this->assertEquals($dto, $prop->getParent());
            $this->assertInstanceOf(Property::class, $prop);
        }

        /** @var Property $intEnum */
        $intEnum = $dto['intEnum'];
        $this->assertEquals('object', $intEnum->getType());
        $this->assertEquals('intEnum', $intEnum->getName());
        $this->assertEquals('int', $intEnum->getSubType());
        $this->assertFalse($intEnum->isCollection());

        /** @var Property $stringEnum */
        $stringEnum = $dto['stringEnum'];
        $this->assertEquals('object', $stringEnum->getType());
        $this->assertEquals('stringEnum', $stringEnum->getName());
        $this->assertEquals('string', $stringEnum->getSubType());
        $this->assertFalse($stringEnum->isCollection());

        /** @var Property $int */
        $int = $dto['myInt'];
        $this->assertEquals('int', $int->getType());
        $this->assertEquals('myInt', $int->getName());
        $this->assertFalse($int->isCollection());

        /** @var Property $intArr */
        $intArr = $dto['intArr'];
        $this->assertEquals('int', $intArr->getType());
        $this->assertEquals('intArr', $intArr->getName());
        $this->assertTrue($intArr->isCollection());

        /** @var Property $string */
        $string = $dto['myString'];
        $this->assertEquals('string', $string->getType());
        $this->assertEquals('myString', $string->getName());
        $this->assertFalse($string->isCollection());

        /** @var Property $date */
        $date = $dto['date'];
        $this->assertEquals('object', $date->getType());
        $this->assertEquals('date', $date->getName());
        $this->assertEquals('string', $date->getSubType());
        $this->assertFalse($date->isCollection());

        /** @var Property $model */
        $model = $dto['model'];
        $this->assertEquals('object', $model->getType());
        $this->assertEquals('model', $model->getName());
        $this->assertFalse($model->isCollection());
        $this->assertEquals(DummyModel::class, $model->getFqcn());

        $this->assertNotNull($find = $model->getFindAnnotation());
        $this->assertEquals([
            'id' => 'id',
            'custom' => '$customProp',
            'date' => 'whatever',
        ], $find->getFields());

        $this->assertCount(2, $find->getTypes());
        $this->assertInstanceOf(Type::class, $type = $find->getTypes()['id'] ?? null);

        $this->assertEquals('int', $type->type);
        $this->assertFalse($type->collection);
        $this->assertNull($type->format);

        $this->assertInstanceOf(Type::class, $date = $find->getTypes()['date'] ?? null);

        $this->assertEquals('object', $date->type);
        $this->assertFalse($date->collection);
        $this->assertNotNull($date->format);

        // check if sub-prop was created
        $this->assertArrayHasKey('id', $model);

        /** @var Property $subProp */
        $subProp = $model['id'];

        $this->assertEquals('int', $subProp->getType());
        $this->assertFalse($subProp->isCollection());

        /** @var Property $subDate */
        $subDate = $model['date'];

        $this->assertEquals('object', $subDate->getType());
        $this->assertFalse($subDate->isCollection());
        $this->assertEquals('string', $subDate->getSubType());
        $this->assertEquals('date', $subDate->getName());

        /** @var Dto $subDto */
        $subDto = $dto['dto'];
        $this->assertEquals('object', $subDto->getType());
        $this->assertEquals('dto', $subDto->getName());
        $this->assertFalse($subDto->isCollection());
        $this->assertEquals(SubDto::class, $subDto->getFqcn());

        foreach (['subDtoInt', 'subDtoFloat', 'subDtoBool'] as $key) {
            $this->assertArrayHasKey($key, $subDto);
        }

        /** @var Property $subInt */
        $subInt = $subDto['subDtoInt'];
        $this->assertEquals('int', $subInt->getType());
        $this->assertEquals('subDtoInt', $subInt->getName());
        $this->assertFalse($subInt->isCollection());

        /** @var Property $subFloat */
        $subFloat = $subDto['subDtoFloat'];
        $this->assertEquals('float', $subFloat->getType());
        $this->assertEquals('subDtoFloat', $subFloat->getName());
        $this->assertFalse($subFloat->isCollection());

        $subBool = $subDto['subDtoBool'];
        $this->assertEquals('bool', $subBool->getType());
        $this->assertEquals('subDtoBool', $subBool->getName());
        $this->assertFalse($subBool->isCollection());
    }
}
