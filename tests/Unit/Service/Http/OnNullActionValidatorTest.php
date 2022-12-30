<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Http;

use DualMedia\DtoRequestBundle\Attributes\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class OnNullActionValidatorTest extends KernelTestCase
{
    private OnNullActionValidator $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(OnNullActionValidator::class);
    }

    /**
     * @testWith [155, false]
     *           [null, true]
     *           ["aaaaaA", false]
     */
    public function testValidation(
        $variable,
        bool $expected
    ): void {
        $this->assertEquals(
            $expected,
            $this->service->validate(new OnNull(Response::HTTP_NOT_FOUND), $variable)
        );
    }

    public function testSupports(): void
    {
        $this->assertTrue(
            $this->service->supports(new OnNull(Response::HTTP_NOT_FOUND), null)
        );
    }
}
