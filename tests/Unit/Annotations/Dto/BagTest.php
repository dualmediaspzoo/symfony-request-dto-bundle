<?php

namespace DM\DtoRequestBundle\Tests\Unit\Annotations\Dto;

use DM\DtoRequestBundle\Attributes\Dto\Bag;
use DM\DtoRequestBundle\Enum\BagEnum;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;

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
