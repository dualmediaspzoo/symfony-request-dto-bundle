<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit;

use DualMedia\DtoRequestBundle\Tests\Traits\Unit\BoundCallableTrait;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    use BoundCallableTrait;

    protected function tearDown(): void
    {
        $this->assertBoundCallables();
        parent::tearDown();
    }
}
