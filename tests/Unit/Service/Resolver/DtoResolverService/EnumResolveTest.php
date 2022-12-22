<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DM\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\EnumByKeysDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\EnumDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\EnumQueryDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\LimitedEnumByKeyDto;
use DM\DtoRequestBundle\Tests\Fixtures\Model\Dto\LimitedEnumDto;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group enum
 */
class EnumResolveTest extends KernelTestCase
{
    private DtoResolverService $service;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testEnumResolve(): void
    {
        $request = new Request([], [
            'int' => 15,
            'string' => 'not_string_key',
        ]);

        /** @var EnumDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            EnumDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals(15, $resolved->int->value);
        $this->assertEquals('not_string_key', $resolved->string->value);
        $this->assertEquals(['int', 'string'], $resolved->getVisited());
        $this->assertTrue($resolved->visited('int'));
        $this->assertTrue($resolved->visited('string'));
    }

    public function testEnumResolveKey(): void
    {
        $request = new Request([], [
            'int' => 'INTEGER_KEY',
            'string' => 'STRING_KEY',
        ]);

        /** @var EnumByKeysDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            EnumByKeysDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals(15, $resolved->int?->value);
        $this->assertEquals('not_string_key', $resolved->string?->value);
        $this->assertEquals(['int', 'string'], $resolved->getVisited());
        $this->assertTrue($resolved->visited('int'));
        $this->assertTrue($resolved->visited('string'));
    }

    public function testResolveQueryEnum(): void
    {
        $request = new Request([
            'int' => '15',
        ]);

        /** @var EnumQueryDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            EnumQueryDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertEquals(15, $resolved->int?->value);
        $this->assertEquals(['int'], $resolved->getVisited());
        $this->assertTrue($resolved->visited('int'));
    }

    public function testLimitedResolve(): void
    {
        $request = new Request([], [
            'int' => '15',
        ]);

        /** @var LimitedEnumDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            LimitedEnumDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertTrue($resolved->visited('int'));
        $this->assertEquals(15, $resolved->int->value);
        $this->assertEquals(['int'], $resolved->getVisited());
    }

    public function testBadValueResolve(): void
    {
        $request = new Request([], [
            'int' => '20',
        ]);

        /** @var LimitedEnumDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            LimitedEnumDto::class
        );

        $this->assertFalse($resolved->isValid());
        $this->assertTrue($resolved->visited('int'));
        $this->assertNull($resolved->int);
        $this->assertEquals(['int'], $resolved->getVisited());
    }

    public function testLimitedKeyResolve(): void
    {
        $request = new Request([], [
            'int' => 'INTEGER_KEY',
        ]);

        /** @var LimitedEnumByKeyDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            LimitedEnumByKeyDto::class
        );

        $this->assertTrue($resolved->isValid());
        $this->assertTrue($resolved->visited('int'));
        $this->assertEquals(15, $resolved->int->value);
        $this->assertEquals(['int'], $resolved->getVisited());
    }

    public function testBadKeyResolve(): void
    {
        $request = new Request([], [
            'int' => 'OTHER_KEY',
        ]);

        /** @var LimitedEnumByKeyDto $resolved */
        $resolved = $this->service->resolve(
            $request,
            LimitedEnumByKeyDto::class
        );

        $this->assertFalse($resolved->isValid());
        $this->assertTrue($resolved->visited('int'));
        $this->assertNull($resolved->int);
        $this->assertEquals(['int'], $resolved->getVisited());
    }
}
