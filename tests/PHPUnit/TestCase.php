<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit;

use DualMedia\DtoRequestBundle\Tests\Traits\Unit\BoundCallableTrait;
use DualMedia\DtoRequestBundle\Tests\Traits\Unit\MockWithCustomMethodsTrait;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    use BoundCallableTrait;
    use MockWithCustomMethodsTrait;

    protected function tearDown(): void
    {
        $this->assertBoundCallables();
        parent::tearDown();
    }
}
