<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Annotations\Dto\AllowEnum;
use DM\DtoRequestBundle\Annotations\Dto\FromKey;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Service\Type\Coercer\EnumCoercer;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\IntegerEnum;
use DM\DtoRequestBundle\Tests\Fixtures\Enum\StringEnum;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use MyCLabs\Enum\Enum;
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
            $this->buildProperty('object', false, Enum::class),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $enum = (new Property())
            ->setType('object')
            ->setFqcn(StringEnum::class);

        $result = $this->service->coerce('something', $enum, StringEnum::STRING_KEY);
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            StringEnum::STRING_KEY,
            $result->getValue()->getValue()
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
            ->addDtoAnnotation(new AllowEnum(['OTHER_KEY', 'LAST_KEY']));

        $result = $this->service->coerce('something', $enum, 20);
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            IntegerEnum::OTHER_KEY,
            $result->getValue()->getValue()
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
        $enum->addDtoAnnotation(new FromKey());

        $result = $this->service->coerce('something', $enum, 'OTHER_KEY');
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            IntegerEnum::OTHER_KEY,
            $result->getValue()->getValue()
        );

        $result = $this->service->coerce('something', $enum, 'INTEGER_KEY');
        $this->assertCount(1, $result->getViolations());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        $this->assertArrayHasKey('something', $mapped);

        $this->assertEquals(
            (new Choice())->message,
            $mapped['something'][0]->getMessage()
        );
    }
}
