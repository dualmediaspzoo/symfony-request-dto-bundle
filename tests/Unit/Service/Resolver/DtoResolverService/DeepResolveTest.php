<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\DeepDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('unit')]
#[Group('service')]
#[Group('resolver')]
#[CoversClass(DtoResolverService::class)]
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

        static::assertTrue($resolved->isValid());
        static::assertTrue($resolved->visited('pathed'));
        static::assertEquals('value', $resolved->pathed);
    }
}
