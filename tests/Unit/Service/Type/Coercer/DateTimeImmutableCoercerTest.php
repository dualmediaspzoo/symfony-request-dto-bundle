<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Attribute\Dto\Format;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Validator\Constraints\DateTime as DateTimeConstraint;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[Group('coercer')]
#[CoversClass(DateTimeImmutableCoercer::class)]
class DateTimeImmutableCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = DateTimeImmutableCoercer::class;

    public static function provideSupportsCases(): iterable
    {
        foreach ([\DateTimeImmutable::class, \DateTimeInterface::class] as $c) {
            foreach ([true, false] as $bool) {
                yield [
                    static::buildProperty('object', $bool, $c),
                    true,
                ];
            }
        }

        yield [
            static::buildProperty('string'),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $date = (new Property())
            ->setType('object')
            ->setFqcn(\DateTimeInterface::class);

        $result = $this->service->coerce('something', $date, '2012-02-12T12:00:00+00:00');
        static::assertEmpty($result->getViolations());

        static::assertEquals(
            '2012-02-12T12:00:00+00:00',
            $result->getValue()->format(\DateTimeInterface::ATOM)
        );
    }

    public function testCustomFormatCoerce(): void
    {
        $date = (new Property())
            ->setType('object')
            ->setFqcn(\DateTimeInterface::class)
            ->setFormat(new Format('Y-m-d H:i:s'));

        $result = $this->service->coerce('something', $date, '2015-02-15 15:30:00');
        static::assertEmpty($result->getViolations());

        static::assertEquals(
            '2015-02-15 15:30:00',
            $result->getValue()->format('Y-m-d H:i:s')
        );
    }

    public function testCustomFormatViolation(): void
    {
        $date = (new Property())
            ->setType('object')
            ->setFqcn(\DateTimeInterface::class)
            ->setFormat(new Format('Y-m-d H:i:s'));

        $result = $this->service->coerce('something', $date, '2015-02-15');
        static::assertCount(1, $result->getViolations());
        static::assertNull($result->getValue());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        static::assertArrayHasKey('something', $mapped);

        static::assertEquals(
            (new DateTimeConstraint())->message,
            $mapped['something'][0]->getMessage()
        );
    }
}
