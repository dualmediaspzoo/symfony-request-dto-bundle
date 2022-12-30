<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\DeepDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class DeepResolveTest extends KernelTestCase
{
    private DtoResolverService $service;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testDeepResolve(): void
    {
        /** @var DeepDto $resolved */
        $resolved = $this->service->resolve(new Request([], [
            'something' => [
                'deep' => 'value',
            ],
        ]), DeepDto::class);

        $this->assertTrue($resolved->isValid());
        $this->assertTrue($resolved->visited('pathed'));
        $this->assertEquals('value', $resolved->pathed);
    }
}
