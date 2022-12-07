<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Constraints;

use DM\DtoRequestBundle\Constraints\ArrayAll;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @group constraint
 */
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

    /**
     * @dataProvider okProvider
     */
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

    public function okProvider(): array
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
