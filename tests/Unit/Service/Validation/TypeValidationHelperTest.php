<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Validation;

use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Validation\TypeValidationHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
#[Group('service')]
#[Group('validation')]
#[CoversClass(TypeValidationHelper::class)]
class TypeValidationHelperTest extends KernelTestCase
{
    private TypeValidationHelper $service;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->service = $this->getService(TypeValidationHelper::class);
    }

    public function testNoErrors(): void
    {
        $values = [
            'value' => 15,
        ];
        $list = $this->service->validateType($values, [
            'value' => (new Property())
                ->setType('int'),
        ]);

        $this->assertCount(0, $list);
    }

    public function testErrors(): void
    {
        $values = [
            'value' => 15,
        ];

        $list = $this->service->validateType($values, [
            'value' => (new Property())
                ->setType('string')
                ->setCollection(true),
        ]);

        $this->assertCount(1, $list);
    }

    public function testIndexRemoval(): void
    {
        $values = [
            'value' => [
                15,
                'string',
                255,
                'other-string',
            ],
        ];

        $list = $this->service->validateType($values, [
            'value' => (new Property())
                ->setType('int')
                ->setCollection(true),
        ]);

        $this->assertCount(2, $list);

        $this->assertEquals([
            'value' => [
                0 => 15,
                2 => 255,
            ],
        ], $values);
    }

    public function testIndexRemovalForSubtypes(): void
    {
        $values = [
            'value' => [
                15,
                'invalid-lmao',
            ],
        ];

        $list = $this->service->validateType($values, [
            'value' => (new Property())
                ->setType('object')
                ->setFqcn(IntegerEnum::class)
                ->setCollection(true)
                ->setSubType('int'),
        ]);

        $this->assertCount(1, $list);
        $this->assertArrayHasKey(0, $values['value']);
        $this->assertInstanceOf(IntegerEnum::class, $values['value'][0]);
        $this->assertEquals(IntegerEnum::IntegerKey->value, $values['value'][0]->value);
    }

    public function testIndexRemovalForSubtypesWithNoCoercion(): void
    {
        $values = [
            'value' => [
                15,
                22,
                'not_string_key',
            ],
        ];

        $list = $this->service->validateType($values, [
            'value' => (new Property())
                ->setType('object')
                ->setFqcn(StringEnum::class)
                ->setCollection(true)
                ->setSubType('string'),
        ]);

        $this->assertCount(2, $list);
        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($list);

        $this->assertArrayHasKey('value[0]', $mapped);
        $this->assertArrayHasKey('value[1]', $mapped);

        $this->assertArrayNotHasKey(0, $values['value']);
        $this->assertArrayNotHasKey(1, $values['value']);
        $this->assertArrayHasKey(2, $values['value']);

        $this->assertInstanceOf(StringEnum::class, $values['value'][2]);
        $this->assertEquals(StringEnum::StringKey->value, $values['value'][2]->value);
    }
}
