<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Nelmio;

use DualMedia\DtoRequestBundle\Tests\Fixtures\Nelmio\DescriptorTest;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\NelmioTestCase;
use OpenApi\Annotations\Post;
use OpenApi\Annotations\RequestBody;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
#[Group('service')]
#[Group('nelmio')]
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
