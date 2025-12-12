<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Type;

use DualMedia\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\CoerceResult;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Service\Type\CoercerService;
use DualMedia\DtoRequestBundle\Tests\Model\ArrayIterator;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Group('unit')]
#[Group('service')]
#[Group('type')]
#[CoversClass(CoercerService::class)]
class CoercerServiceTest extends TestCase
{
    #[TestWith([true])]
    #[TestWith([false])]
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

    #[TestWith([true, 15, 20])]
    #[TestWith([true, 25, 444])]
    #[TestWith([false, 15, null])]
    #[TestWith([false, 'string', null])]
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
            $result?->getValue()
        );
    }
}
