<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Nelmio;

use DM\DtoRequestBundle\Tests\Fixtures\Nelmio\DescriptorTest;
use DM\DtoRequestBundle\Tests\PHPUnit\NelmioTestCase;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\RequestBody;

class DtoOADescriberTest extends NelmioTestCase
{
    public function testDescribeComplex(): void
    {
        $api = $this->describe(DescriptorTest::class, 'testMethod');

        $this->assertCount(1, $api->paths);
        $path = $api->paths[0];

        $this->assertEquals('/some-path', $path->path);
        $this->assertInstanceOf(Post::class, $path->post);
        $this->assertInstanceOf(RequestBody::class, $path->post->requestBody);
    }
}
