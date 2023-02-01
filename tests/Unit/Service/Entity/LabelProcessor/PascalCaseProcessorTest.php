<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity\LabelProcessor;

use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\PascalCaseProcessor;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;

class PascalCaseProcessorTest extends TestCase
{
    private PascalCaseProcessor $service;

    protected function setUp(): void
    {
        $this->service = new PascalCaseProcessor();
    }

    /**
     * @testWith ["MyLabel", "MY_LABEL"]
     *           ["OtherWeirdLabel", "OTHER_WEIRD_LABEL"]
     */
    public function testNormalize(
        string $input,
        string $output
    ): void {
        $this->assertEquals(
            $output,
            $this->service->normalize($input)
        );
    }

    /**
     * @testWith ["MY_LABEL", "MyLabel"]
     *           ["OTHER_WEIRD_LABEL", "OtherWeirdLabel"]
     */
    public function testDenormalize(
        string $input,
        string $output
    ): void {
        $this->assertEquals(
            $output,
            $this->service->denormalize($input)
        );
    }
}
