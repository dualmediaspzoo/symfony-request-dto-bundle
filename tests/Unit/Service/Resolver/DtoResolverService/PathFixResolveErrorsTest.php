<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Resolver\DtoResolverService;

use DM\DtoRequestBundle\Service\Resolver\DtoResolverService;
use DM\DtoRequestBundle\Tests\Fixtures\Model\PathFixDto\PathFixDto;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * @group fix-paths-test
 */
class PathFixResolveErrorsTest extends KernelTestCase
{
    private DtoResolverService $service;

    protected function setUp(): void
    {
        parent::bootKernel();
        $this->service = $this->getService(DtoResolverService::class);
    }

    public function testValidity(): void
    {
        $resolved = $this->service->resolve(new Request(), PathFixDto::class);

        $this->assertFalse($resolved->isValid());
        $this->assertCount(2, $resolved->getConstraintViolationList());

        /** @var ConstraintViolationInterface $violation */
        $violation = $resolved->getConstraintViolationList()[0];
        $this->assertEquals('integer', $violation->getPropertyPath());

        /** @var ConstraintViolationInterface $violation */
        $violation = $resolved->getConstraintViolationList()[1];
        $this->assertEquals('other_string_path', $violation->getPropertyPath());
    }
}
