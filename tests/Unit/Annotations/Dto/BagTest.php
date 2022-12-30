<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Annotations\Dto;

use DualMedia\DtoRequestBundle\Attributes\Dto\Bag;
use DualMedia\DtoRequestBundle\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;

class BagTest extends TestCase
{
    /**
     * @testWith ["query"]
     *           ["request"]
     *           ["attributes"]
     *           ["files"]
     *           ["cookies"]
     *           ["headers", true]
     */
    public function testCreation(
        string $bag,
        bool $isHeader = false
    ): void {
        $annotation = new Bag(BagEnum::from($bag));

        $this->assertEquals(
            $bag,
            $annotation->bag->value
        );
        $this->assertEquals(
            $isHeader,
            $annotation->bag->isHeaders()
        );
    }
}
