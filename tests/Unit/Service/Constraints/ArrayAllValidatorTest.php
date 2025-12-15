<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Constraints;

use DualMedia\DtoRequestBundle\Constraint\ArrayAll;
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
        static::assertCount(
            1,
            $list = $this->service->validate(
                15,
                new ArrayAll()
            )
        );
        /** @var ConstraintViolationInterface $violation */
        $violation = $list[0];
        static::assertEquals('This value should be of type iterable.', $violation->getMessage());
        static::assertEquals(15, $violation->getInvalidValue());
    }

    #[DataProvider('provideOkCases')]
    public function testOk(
        $input
    ): void {
        static::assertCount(
            0,
            $this->service->validate(
                $input,
                new ArrayAll()
            )
        );
    }

    public static function provideOkCases(): iterable
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
