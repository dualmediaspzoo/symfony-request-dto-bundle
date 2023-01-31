<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attributes\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Attributes\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use Symfony\Component\Validator\Constraints\Choice;

class EnumCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = EnumCoercer::class;

    public function supportsProvider(): iterable
    {
        foreach ([StringEnum::class, IntegerEnum::class] as $c) {
            foreach ([true, false] as $bool) {
                yield [
                    $this->buildProperty('object', false, $c),
                    true,
                ];
                yield [
                    $this->buildProperty('object', true, $c),
                    true,
                ];
            }
        }

        yield [
            $this->buildProperty('object', false, \BackedEnum::class),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(StringEnum::class);

        $result = $this->service->coerce('something', $enum, StringEnum::StringKey->value);
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            StringEnum::StringKey,
            $result->getValue()
        );
    }

    public function testNullAsNothing(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(StringEnum::class);

        $result = $this->service->coerce('something', $enum, null);
        $this->assertEmpty($result->getViolations());
        $this->assertNull($result->getValue());
    }

    public function testLimited(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(IntegerEnum::class)
            ->addDtoAttribute(new AllowEnum([IntegerEnum::OtherKey, IntegerEnum::LastKey]));

        $result = $this->service->coerce('something', $enum, 20);
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            IntegerEnum::OtherKey,
            $result->getValue()
        );

        $result = $this->service->coerce('something', $enum, 15);
        $this->assertCount(1, $result->getViolations());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        $this->assertArrayHasKey('something', $mapped);

        $this->assertEquals(
            (new Choice())->message,
            $mapped['something'][0]->getMessage()
        );

        // test the same but as keys
        $enum->addDtoAttribute(new FromKey());

        $result = $this->service->coerce('something', $enum, 'OtherKey');
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            IntegerEnum::OtherKey,
            $result->getValue()
        );

        $result = $this->service->coerce('something', $enum, 'IntegerKey');
        $this->assertCount(1, $result->getViolations());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        $this->assertArrayHasKey('something', $mapped);

        $this->assertEquals(
            (new Choice())->message,
            $mapped['something'][0]->getMessage()
        );
    }
}
