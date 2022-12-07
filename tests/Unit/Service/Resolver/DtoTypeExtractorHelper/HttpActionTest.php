<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DM\DtoRequestBundle\Annotations\Dto\Http\OnNull;
use DM\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DM\DtoRequestBundle\Tests\Fixtures\Model\HttpAction\OnNullDto;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;

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
