<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext\WhenVisitedDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group when-visited
 */
class WhenVisitedTest extends KernelTestCase
{
    private DtoResolverService $resolver;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resolver = $this->getService(DtoResolverService::class);
    }

    public function testNullableValueInRequest(): void
    {
        $dto = $this->resolver->resolve(
            new Request(request: [
                'nullableValue' => null,
            ]),
            WhenVisitedDto::class
        );

        $this->assertCount(1, $dto->getConstraintViolationList());
        $this->assertEquals(
            'This value should not be null.',
            $dto->getConstraintViolationList()[0]->getMessage()
        );
    }

    public function testNullableValueNotInRequest(): void
    {
        $dto = $this->resolver->resolve(
            new Request(),
            WhenVisitedDto::class
        );

        $this->assertCount(0, $dto->getConstraintViolationList());
    }
}
