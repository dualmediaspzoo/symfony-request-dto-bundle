<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type\Coercer;

use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Service\Type\Coercer\UploadedFileCoercer;
use DM\DtoRequestBundle\Tests\PHPUnit\Coercer\AbstractMinimalCoercerTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileCoercerTest extends AbstractMinimalCoercerTestCase
{
    protected const SERVICE_ID = UploadedFileCoercer::class;

    public function supportsProvider(): iterable
    {
        yield [
            $this->buildProperty('object', false, UploadedFile::class),
            true,
        ];
        yield [
            $this->buildProperty('object', true, UploadedFile::class),
            true,
        ];

        yield [
            $this->buildProperty('object', false, \DateTime::class),
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
