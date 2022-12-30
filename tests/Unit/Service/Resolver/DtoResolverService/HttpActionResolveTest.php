<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction\FindOneOnNullDto;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction\OnNullDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class HttpActionResolveTest extends KernelTestCase
{
    private DtoResolverService $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testOnNull(): void
    {
        $resolved = $this->service->resolve(new Request(), OnNullDto::class);

        $this->assertTrue($resolved->isValid());
        $this->assertInstanceOf(OnNull::class, $resolved->getHttpAction());
    }

    public function testFindByOnNull(): void
    {
        $resolved = $this->service->resolve(new Request(), FindOneOnNullDto::class);

        $this->assertTrue($resolved->isValid());
        $this->assertInstanceOf(OnNull::class, $resolved->getHttpAction());
    }
}
