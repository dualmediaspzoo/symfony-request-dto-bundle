<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoTypeExtractorHelper;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Service\Resolver\DtoTypeExtractorHelper;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\HttpAction\OnNullDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
#[Group('service')]
#[Group('resolver')]
#[CoversClass(DtoTypeExtractorHelper::class)]
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
