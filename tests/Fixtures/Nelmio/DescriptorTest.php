<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Nelmio;

use DualMedia\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\MainPathFixDto;
use Symfony\Component\Routing\Annotation\Route;

class DescriptorTest
{
    /**
     * @Route("/some-path", methods={"POST"}, name="testMethod")
     */
    public function testMethod(
        MainPathFixDto $dto
    ): void {
    }
}
