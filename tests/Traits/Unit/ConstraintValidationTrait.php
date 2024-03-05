<?php

namespace DualMedia\DtoRequestBundle\Tests\Traits\Unit;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ConstraintValidationTrait
{
    /**
     * @param array<array-key, string|array{0: string, 1: string}> $constraints
     */
    protected function assertConstraintList(
        ConstraintViolationListInterface $list,
        int $count,
        array $constraints = []
    ): void {
        $this->assertCount($count, $list);
        $counter = 0;

        foreach ($constraints as $property => $message) {
            $violation = $list->get($counter++);

            if (is_array($message)) {
                [$property, $message] = $message;
            }

            $this->assertEquals(
                $property,
                $violation->getPropertyPath()
            );
            $this->assertEquals(
                $message,
                $violation->getMessage()
            );
        }
    }
}
