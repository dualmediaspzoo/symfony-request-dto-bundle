<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[Group('coercer')]
#[CoversClass(UploadedFileCoercer::class)]
class UploadedFileCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = UploadedFileCoercer::class;

    public static function provideSupportsCases(): iterable
    {
        yield [
            static::buildProperty('object', false, UploadedFile::class),
            true,
        ];
        yield [
            static::buildProperty('object', true, UploadedFile::class),
            true,
        ];

        yield [
            static::buildProperty('object', false, \DateTime::class),
            false,
        ];
    }

    public function testCoerce(): void
    {
        $fileProp = (new Property())
            ->setType('object')
            ->setFqcn(UploadedFile::class);

        $result = $this->service->coerce('something', $fileProp, [$mock = $this->createMock(UploadedFile::class)]);
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            $mock,
            $result->getValue()
        );
    }

    public function testMultiCoerce(): void
    {
        $fileProp = (new Property())
            ->setType('object')
            ->setFqcn(UploadedFile::class)
            ->setCollection(true);

        $data = [
            $this->createMock(UploadedFile::class),
            $this->createMock(UploadedFile::class),
        ];

        $result = $this->service->coerce(
            'something',
            $fileProp,
            $data
        );
        $this->assertEmpty($result->getViolations());

        $this->assertEquals(
            $data,
            $result->getValue()
        );
    }

    public function testNullAsNothing(): void
    {
        $fileProp = (new Property())
            ->setType('object')
            ->setFqcn(UploadedFile::class);

        $result = $this->service->coerce('something', $fileProp, null);
        $this->assertEmpty($result->getViolations());
        $this->assertNull($result->getValue());
    }
}
