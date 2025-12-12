<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DualMedia\DtoRequestBundle\Constraints as DtoAssert;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\ResolveDto\BaseDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[Group('unit')]
#[Group('service')]
#[Group('resolver')]
#[CoversClass(DtoResolverService::class)]
class BasicResolveAndErrorsTest extends KernelTestCase
{
    private DtoResolverService $service;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testOnlyFieldResolve(): void
    {
        $request = new Request([], [
            'field' => 'whatever',
        ]);

        /** @var BaseDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            BaseDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals('whatever', $resolved->field);
        $this->assertEquals(['field', 'subBase'], $resolved->getVisited());
        $this->assertTrue($resolved->visited('field'));
    }

    public function testSubDtoResolve(): void
    {
        $request = new Request([], [
            'subBase' => [
                'value' => 162,
                'floaty_boy' => 15.2,
            ],
        ]);

        /** @var BaseDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            BaseDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals(162, $resolved->subBase->value);
        $this->assertEquals(15.2, $resolved->subBase->floatVal);
        $this->assertEquals(['subBase'], $resolved->getVisited());
        $this->assertEquals(['value', 'floatVal'], $resolved->subBase->getVisited());
        $this->assertFalse($resolved->visited('field'));
        $this->assertTrue($resolved->visited('subBase'));
    }

    public function testArraySubResolve(): void
    {
        $request = new Request([], [
            'array' => [
                [
                    'value' => 14,
                    'floaty_boy' => 55.5,
                ],
                [
                    'value' => 22,
                    'floaty_boy' => 13.2,
                ],
            ],
        ]);

        /** @var BaseDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            BaseDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals(['subBase', 'subDtos'], $resolved->getVisited());
        $this->assertCount(2, $resolved->subDtos);

        $this->assertEquals(14, $resolved->subDtos[0]->value);
        $this->assertEquals(55.5, $resolved->subDtos[0]->floatVal);

        $this->assertEquals(22, $resolved->subDtos[1]->value);
        $this->assertEquals(13.2, $resolved->subDtos[1]->floatVal);
    }

    public function testBadInputAndPathCorrection(): void
    {
        $request = new Request([], [
            'field' => 155,
            'subBase' => [
                'value' => 'string',
                'floaty_boy' => false,
            ],
            'array' => [
                'value' => 'string',
                'floaty_boy' => false,
            ],
            'other' => [
                [
                    'value' => 'string',
                    'floaty_boy' => false,
                ],
            ],
        ]);

        /** @var BaseDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            BaseDto::class
        );

        $this->assertFalse($resolved->isValid());
        $this->assertCount(6, $resolved->getConstraintViolationList());
        $mapped = $this->getConstraintViolationsMappedToPropertyPaths($resolved->getConstraintViolationList());

        $this->assertArrayHasKey('field', $mapped);
        $this->assertCount(1, $mapped['field']);

        $this->assertEquals(Assert\Type::INVALID_TYPE_ERROR, $mapped['field'][0]->getCode());
        $this->assertEquals('This value should be of type string.', $mapped['field'][0]->getMessage());

        $this->assertArrayHasKey('subBase.value', $mapped);
        $this->assertCount(1, $mapped['subBase.value']);
        $this->assertEquals(Assert\Type::INVALID_TYPE_ERROR, $mapped['subBase.value'][0]->getCode());
        $this->assertEquals('This value should be of type int.', $mapped['subBase.value'][0]->getMessage());

        $this->assertArrayHasKey('subBase.floaty_boy', $mapped);
        $this->assertCount(1, $mapped['subBase.floaty_boy']);
        $this->assertEquals(Assert\Type::INVALID_TYPE_ERROR, $mapped['subBase.floaty_boy'][0]->getCode());
        $this->assertEquals('This value should be of type float.', $mapped['subBase.floaty_boy'][0]->getMessage());

        $this->assertArrayHasKey('array', $mapped);
        $this->assertCount(1, $mapped['array']);
        $this->assertEquals(DtoAssert\ArrayAll::NOT_ALL_ELEMENTS_ARE_ARRAY_ERROR, $mapped['array'][0]->getCode());
        $this->assertEquals('This field must be an array of arrays.', $mapped['array'][0]->getMessage());

        $this->assertArrayHasKey('other[0].value', $mapped);
        $this->assertCount(1, $mapped['other[0].value']);
        $this->assertEquals(Assert\Type::INVALID_TYPE_ERROR, $mapped['other[0].value'][0]->getCode());
        $this->assertEquals('This value should be of type int.', $mapped['other[0].value'][0]->getMessage());

        $this->assertArrayHasKey('other[0].floaty_boy', $mapped);
        $this->assertCount(1, $mapped['other[0].floaty_boy']);
        $this->assertEquals(Assert\Type::INVALID_TYPE_ERROR, $mapped['other[0].floaty_boy'][0]->getCode());
        $this->assertEquals('This value should be of type float.', $mapped['other[0].floaty_boy'][0]->getMessage());
    }
}
