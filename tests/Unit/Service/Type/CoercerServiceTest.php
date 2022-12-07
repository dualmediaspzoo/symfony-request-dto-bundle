<?php

namespace DM\DtoRequestBundle\Tests\Unit\Service\Type;

use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\CoerceResult;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Service\Type\CoercerService;
use DM\DtoRequestBundle\Tests\Model\ArrayIterator;
use DM\DtoRequestBundle\Tests\PHPUnit\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CoercerServiceTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     */
    public function testSupports(
        bool $result
    ): void {
        $property = $this->createMock(Property::class);

        $check = $this->deferCallable(function (Property $input) use ($property) {
            $this->assertEquals($property, $input);
        });

        $mock = $this->createMock(CoercerInterface::class);
        $mock->expects($this->once())
            ->method('supports')
            ->willReturnCallback(function (...$args) use ($check, $result) {
                $check->set($args);

                return $result;
            });

        $service = new CoercerService(new ArrayIterator([$mock]), $this->createMock(ValidatorInterface::class));
        $this->assertEquals(
            $result,
            $service->supports($property)
        );
    }

    /**
     * @testWith [true, 15, 20]
     *           [true, 25, 444]
     *           [false, 15, null]
     *           [false, "string", null]
     */
    public function testCoerce(
        bool $supports,
        $input,
        $output
    ): void {
        $property = $this->createMock(Property::class);

        $check = $this->deferCallable(function (Property $input) use ($property) {
            $this->assertEquals($property, $input);
        });

        $mock = $this->createMock(CoercerInterface::class);
        $mock->expects($this->once())
            ->method('supports')
            ->willReturnCallback(function (...$args) use ($check, $supports) {
                $check->set($args);

                return $supports;
            });

        if ($supports) {
            $coerce = $this->deferCallable(function (string $name, Property $prop, $in) use ($property, $input) {
                $this->assertEquals('something', $name);
                $this->assertEquals($property, $prop);
                $this->assertEquals($input, $in);
            });

            $mock->expects($this->once())
                ->method('coerce')
                ->willReturnCallback(function (...$args) use ($coerce, $output) {
                    $coerce->set($args);

                    $result = $this->createMock(CoerceResult::class);
                    $result->method('getValue')
                        ->willReturn($output);

                    return $result;
                });
        }

        $service = new CoercerService(new ArrayIterator([$mock]), $this->createMock(ValidatorInterface::class));
        $result = $service->coerce('something', $property, $input);

        $this->assertEquals(
            $output,
            null !== $result ? $result->getValue() : null
        );
    }
}
