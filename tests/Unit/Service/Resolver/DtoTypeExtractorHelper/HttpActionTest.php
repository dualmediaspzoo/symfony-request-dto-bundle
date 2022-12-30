<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction\OnNullDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;

class HttpActionTest extends KernelTestCase
{
    private DtoTypeExtractorHelper $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(DtoTypeExtractorHelper::class);
    }

    public function testRead(): void
    {
        $type = $this->service->extract(new \ReflectionClass(OnNullDto::class));

        $this->assertArrayHasKey('model', $type);
        $this->assertInstanceOf(OnNull::class, $type['model']->getHttpAction());
    }
}
