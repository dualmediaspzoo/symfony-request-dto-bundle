<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext\MappedToDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * This tests checks if the validation is being performed as expected on Symfony objects.
 */
#[Group('unit')]
#[Group('dto')]
#[Group('validation-context')]
class MappedToTest extends KernelTestCase
{
    private DtoResolverService $resolver;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resolver = $this->getService(DtoResolverService::class);
    }

    public function testResolve(): void
    {
        $dto = $this->resolver->resolve(
            new Request(),
            MappedToDto::class
        );

        $this->assertNull($dto->intVal);
        $this->assertCount(2, $dto->getConstraintViolationList());

        $this->assertEquals(
            'Expression failed, intVal is null',
            $dto->getConstraintViolationList()[0]->getMessage()
        );
    }
}
