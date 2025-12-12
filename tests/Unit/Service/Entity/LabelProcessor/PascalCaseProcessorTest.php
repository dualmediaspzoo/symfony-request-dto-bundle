<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity\LabelProcessor;

use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\PascalCaseProcessor;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;

#[Group('unit')]
#[Group('service')]
#[Group('entity')]
#[Group('label-processor')]
#[CoversClass(PascalCaseProcessor::class)]
class PascalCaseProcessorTest extends TestCase
{
    private PascalCaseProcessor $service;

    protected function setUp(): void
    {
        $this->service = new PascalCaseProcessor();
    }

    #[TestWith(['MyLabel', 'MY_LABEL'])]
    #[TestWith(['OtherWeirdLabel', 'OTHER_WEIRD_LABEL'])]
    public function testNormalize(
        string $input,
        string $output
    ): void {
        static::assertEquals(
            $output,
            $this->service->normalize($input)
        );
    }

    #[TestWith(['MY_LABEL', 'MyLabel'])]
    #[TestWith(['OTHER_WEIRD_LABEL', 'OtherWeirdLabel'])]
    public function testDenormalize(
        string $input,
        string $output
    ): void {
        static::assertEquals(
            $output,
            $this->service->denormalize($input)
        );
    }
}
