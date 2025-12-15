<?php

namespace DualMedia\DtoRequestBundle\Tests\PHPUnit\Coercer;

use DualMedia\DtoRequestBundle\Interface\Type\CoercerInterface;
use DualMedia\DtoRequestBundle\Model\Type\Property;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class AbstractMinimalCoercerTestCase extends KernelTestCase
{
    protected const array BASIC_TYPES = [
        'string',
        'int',
        'bool',
        'float',
        'object',
        'array',
    ];

    protected const SERVICE_ID = null;

    protected CoercerInterface $service;

    protected function setUp(): void
    {
        if (null === static::SERVICE_ID) {
            static::fail('No service id is set');
        }

        self::bootKernel();
        $this->service = $this->getService(static::SERVICE_ID);
    }

    #[DataProvider('provideSupportsCases')]
    public function testSupports(
        Property $property,
        bool $supports
    ): void {
        static::assertEquals(
            $supports,
            $this->service->supports($property)
        );
    }

    /**
     * @return iterable<array{0: Property, 1: bool}>
     */
    abstract public static function provideSupportsCases(): iterable;

    protected static function buildProperty(
        string $type,
        bool $isCollection = false,
        string|null $class = null
    ): Property {
        return (new Property())
            ->setType($type)
            ->setCollection($isCollection)
            ->setFqcn($class);
    }
}
