<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Constraints;

use DualMedia\DtoRequestBundle\Constraints\ArrayAll;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Group('unit')]
#[Group('service')]
#[Group('constraints')]
class ArrayAllValidatorTest extends KernelTestCase
{
    private ValidatorInterface $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService('validator');
    }

    public function testUnexpectedValue(): void
    {
        $this->assertCount(
            1,
            $list = $this->service->validate(
                15,
                new ArrayAll()
            )
        );
        /** @var ConstraintViolationInterface $violation */
        $violation = $list[0];
        $this->assertEquals('This value should be of type iterable.', $violation->getMessage());
        $this->assertEquals(15, $violation->getInvalidValue());
    }

    #[DataProvider('provideOkCases')]
    public function testOk(
        $input
    ): void {
        $this->assertCount(
            0,
            $this->service->validate(
                $input,
                new ArrayAll()
            )
        );
    }

    public static function provideOkCases(): array
    {
        return [
            [null],
            [[]],
            [[[], []]],
            [[
                [
                    'input' => 15,
                ],
                [
                    'semi-invalid' => false,
                ],
            ]],
        ];
    }
}
