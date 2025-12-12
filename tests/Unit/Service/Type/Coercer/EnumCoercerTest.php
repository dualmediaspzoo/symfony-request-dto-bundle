<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attribute\Dto\AllowEnum;
use DualMedia\DtoRequestBundle\Attribute\Dto\FromKey;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Validator\Constraints\Choice;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[Group('coercer')]
#[CoversClass(EnumCoercer::class)]
class EnumCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = EnumCoercer::class;

    public static function provideSupportsCases(): iterable
    {
        foreach ([StringEnum::class, IntegerEnum::class] as $c) {
            foreach ([true, false] as $bool) {
                yield [
                    static::buildProperty('object', false, $c),
                    true,
                ];
                yield [
                    static::buildProperty('object', true, $c),
                    true,
                ];
            }
        }

        yield [
            static::buildProperty('object', false, \BackedEnum::class),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(StringEnum::class);

        $result = $this->service->coerce('something', $enum, StringEnum::StringKey->value);
        static::assertEmpty($result->getViolations());

        static::assertEquals(
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
        static::assertEmpty($result->getViolations());
        static::assertNull($result->getValue());
    }

    public function testLimited(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(IntegerEnum::class)
            ->addDtoAttribute(new AllowEnum([IntegerEnum::OtherKey, IntegerEnum::LastKey]));

        $result = $this->service->coerce('something', $enum, 20);
        static::assertEmpty($result->getViolations());

        static::assertEquals(
            IntegerEnum::OtherKey,
            $result->getValue()
        );

        $result = $this->service->coerce('something', $enum, 15);
        static::assertCount(1, $result->getViolations());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        static::assertArrayHasKey('something', $mapped);

        static::assertEquals(
            (new Choice())->message,
            $mapped['something'][0]->getMessage()
        );

        // test the same but as keys
        $enum->addDtoAttribute(new FromKey());

        $result = $this->service->coerce('something', $enum, 'OtherKey');
        static::assertEmpty($result->getViolations());

        static::assertEquals(
            IntegerEnum::OtherKey,
            $result->getValue()
        );

        $result = $this->service->coerce('something', $enum, 'IntegerKey');
        static::assertCount(1, $result->getViolations());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        static::assertArrayHasKey('something', $mapped);

        static::assertEquals(
            (new Choice())->message,
            $mapped['something'][0]->getMessage()
        );
    }
}
