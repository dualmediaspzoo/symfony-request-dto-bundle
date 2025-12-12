<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Attributes\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Bag;
use DualMedia\DtoRequestBundle\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

class BagTest extends TestCase
{
    #[TestWith(['query'])]
    #[TestWith(['request'])]
    #[TestWith(['attributes'])]
    #[TestWith(['files'])]
    #[TestWith(['cookies'])]
    #[TestWith(['headers', true])]
    public function testCreation(
        string $bag,
        bool $isHeader = false
    ): void {
        $annotation = new Bag(BagEnum::from($bag));

        static::assertEquals(
            $bag,
            $annotation->bag->value
        );
        static::assertEquals(
            $isHeader,
            $annotation->bag->isHeaders()
        );
    }
}
