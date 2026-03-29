<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit;

use DualMedia\DtoRequestBundle\Tests\Trait\ConstraintValidationTrait;
use DualMedia\DtoRequestBundle\Tests\Trait\KernelAccessTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as SymfonyTestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class KernelTestCase extends SymfonyTestCase
{
    use KernelAccessTrait;
    use ConstraintValidationTrait;

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

    /**
     * @return array<string, list<ConstraintViolationInterface>>
     */
    protected static function getConstraintViolationsMappedToPropertyPaths(
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
