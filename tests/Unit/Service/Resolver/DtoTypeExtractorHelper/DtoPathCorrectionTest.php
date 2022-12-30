<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DualMedia\DtoRequestBundle\Model\Type\Dto;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\MainPathFixDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\PathFindByFix;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\PathFixDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\RequestEdgeCaseDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @group test-path-correction
 */
class DtoPathCorrectionTest extends KernelTestCase
{
    private DtoTypeExtractorHelper $service;
    private Dto $model;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(DtoTypeExtractorHelper::class);
    }

    public function testSimplePathCorrection(): void
    {
        $this->model = $this->service->extract(new \ReflectionClass(PathFixDto::class));

        $this->assertPropertyPath(
            'integer',
            'integer',
        );
        $this->assertPropertyPath(
            'other_string_path',
            'string'
        );
    }

    public function testMultiPathCorrection(): void
    {
        $this->model = $this->service->extract(new \ReflectionClass(MainPathFixDto::class));

        $this->assertPropertyPath(
            'fix.other_string_path',
            'fix.string'
        );
        $this->assertPropertyPath(
            'fix.integer',
            'fix.integer'
        );
        $this->assertPropertyPath(
            'other_fix_path.other_string_path',
            'pathFix.string'
        );
        $this->assertPropertyPath(
            'other_fix_path.integer',
            'pathFix.integer'
        );
        $this->assertPropertyPath(
            'nonFixArray[0].other_string_path',
            'nonFixArray[0].string'
        );
        $this->assertPropertyPath(
            'nonFixArray[0].integer',
            'nonFixArray[0].integer'
        );
        $this->assertPropertyPath(
            'some_fix_path_array[9].other_string_path',
            'fixArray[9].string'
        );
        $this->assertPropertyPath(
            'some_fix_path_array[9].integer',
            'fixArray[9].integer'
        );
    }

    public function testRequestEmptyPathEdgeCase(): void
    {
        $this->model = $this->service->extract(new \ReflectionClass(RequestEdgeCaseDto::class));

        $this->assertPropertyPath(
            '[2].other_string_path',
            'dtos[2].string'
        );
    }

    public function testFindCorrect(): void
    {
        $this->model = $this->service->extract(new \ReflectionClass(PathFindByFix::class));

        $this->assertPropertyPath(
            'whatever',
            'dummy'
        );
        $this->assertPropertyPath(
            'overrideError',
            'dummy2'
        );
        $this->assertPropertyPath(
            'whatever[0]',
            'dummies[0]'
        );
        $this->assertPropertyPath(
            'overrideError[0]',
            'otherDummies[0]'
        );
        $this->assertPropertyPath(
            'overrideError[0]',
            'superDummies[0]'
        );
    }

    private function assertPropertyPath(
        string $expected,
        string $propertyPath
    ): void {
        $pp = new PropertyPath($propertyPath);

        $this->assertEquals(
            $expected,
            $this->model[$pp->getElement(0)]->fixPropertyPath($pp)
        );
    }
}
