<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit;

use DualMedia\DtoRequestBundle\Tests\Traits\Unit\BoundCallableTrait;
use DualMedia\DtoRequestBundle\Tests\Traits\Unit\KernelAccessTrait;
use DualMedia\DtoRequestBundle\Tests\Traits\Unit\MockWithCustomMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyTestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class KernelTestCase extends SymfonyTestCase
{
    use KernelAccessTrait;
    use BoundCallableTrait;
    use MockWithCustomMethodsTrait;

    protected function tearDown(): void
    {
        $this->assertBoundCallables();
        parent::tearDown();
    }

    /**
     * @param ConstraintViolationListInterface $list
     *
     * @return array<string, list<ConstraintViolationInterface>>
     */
    protected function getConstraintViolationsMappedToPropertyPaths(
        ConstraintViolationListInterface $list
    ): array {
        /** @var array<string, list<ConstraintViolationInterface>> $out */
        $out = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($list as $violation) {
            if (!array_key_exists($violation->getPropertyPath(), $out)) {
                $out[$violation->getPropertyPath()] = [];
            }

            $out[$violation->getPropertyPath()][] = $violation;
        }

        return $out;
    }
}
