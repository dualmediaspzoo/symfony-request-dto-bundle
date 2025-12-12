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

        static::assertCount(0, $list);
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

        static::assertCount(1, $list);
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

        static::assertCount(2, $list);

        static::assertEquals([
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

        static::assertCount(1, $list);
        static::assertArrayHasKey(0, $values['value']);
        static::assertInstanceOf(IntegerEnum::class, $values['value'][0]);
        static::assertEquals(IntegerEnum::IntegerKey->value, $values['value'][0]->value);
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

        static::assertCount(2, $list);
        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($list);

        static::assertArrayHasKey('value[0]', $mapped);
        static::assertArrayHasKey('value[1]', $mapped);

        static::assertArrayNotHasKey(0, $values['value']);
        static::assertArrayNotHasKey(1, $values['value']);
        static::assertArrayHasKey(2, $values['value']);

        static::assertInstanceOf(StringEnum::class, $values['value'][2]);
        static::assertEquals(StringEnum::StringKey->value, $values['value'][2]->value);
    }
}
