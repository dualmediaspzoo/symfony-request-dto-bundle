<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext\MainDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use DualMedia\DtoRequestBundle\Tests\Traits\Unit\ConstraintValidationTrait;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('unit')]
#[Group('dto')]
#[Group('validation-context')]
class DtoWithChildrenContextTest extends KernelTestCase
{
    use ConstraintValidationTrait;

    private DtoResolverService $resolver;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->resolver = $this->getService(DtoResolverService::class);
    }

    public function testFail(): void
    {
        $dto = $this->resolver->resolve(
            new Request(),
            MainDto::class
        );

        $this->assertConstraintList(
            $dto->getConstraintViolationList(),
            3,
            [
                'root' => 'Expression failed, intVal is null',
                'intVal' => 'This value should not be null.',
                'dto' => 'This collection should contain 1 element or more.',
            ]
        );
    }

    public function testPartialSuccess(): void
    {
        $dto = $this->resolver->resolve(
            new Request(request: [
                'dto' => [
                    [],
                ],
                'intVal' => 3,
            ]),
            MainDto::class
        );

        $this->assertConstraintList(
            $dto->getConstraintViolationList(),
            4,
            [
                'intVal' => 'This value should be greater than or equal to 15.',
                'dto[0]' => 'Value must not be null',
                'dto[0].something' => 'Subpathed value',
                'dto[0].value' => 'This value should not be blank.',
            ]
        );
    }
}
