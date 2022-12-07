<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Annotations\Dto\Format;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Service\Type\Coercer\DateTimeImmutableCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use Symfony\Component\Validator\Constraints\DateTime as DateTimeConstraint;

class DateTimeImmutableCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = DateTimeImmutableCoercer::class;

    public function supportsProvider(): iterable
    {
        foreach ([\DateTimeImmutable::class, \DateTimeInterface::class,] as $c) {
            foreach ([true, false] as $bool) {
                yield [
                    $this->buildProperty('object', $bool, $c),
                    true,
                ];
            }
        }

        yield [
            $this->buildProperty('string'),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $date = (new Property())
            ->setType('object')
            ->setFqcn(\DateTimeInterface::class);

        $result = $this->service->coerce('something', $date, '2012-02-12T12:00:00+00:00');
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
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
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
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
        $this->assertCount(1, $result->getViolations());
        $this->assertNull($result->getValue());

        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($result->getViolations());
        $this->assertArrayHasKey('something', $mapped);

        $this->assertEquals(
            (new DateTimeConstraint())->message,
            $mapped['something'][0]->getMessage()
        );
    }
}
