<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Http;

use DualMedia\DtoRequestBundle\Attribute\Dto\Http\OnNull;
use DualMedia\DtoRequestBundle\Service\Http\OnNullActionValidator;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\HttpFoundation\Response;

#[Group('unit')]
#[Group('service')]
#[Group('http')]
#[CoversClass(OnNullActionValidator::class)]
class OnNullActionValidatorTest extends KernelTestCase
{
    private OnNullActionValidator $service;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->service = $this->getService(OnNullActionValidator::class);
    }

    #[TestWith([155, false])]
    #[TestWith([null, true])]
    #[TestWith(['aaaAAA', false])]
    public function testValidation(
        mixed $variable,
        bool $expected
    ): void {
        static::assertEquals(
            $expected,
            $this->service->validate(new OnNull(Response::HTTP_NOT_FOUND), $variable)
        );
    }

    public function testSupports(): void
    {
        static::assertTrue(
            $this->service->supports(new OnNull(Response::HTTP_NOT_FOUND), null)
        );
    }
}
