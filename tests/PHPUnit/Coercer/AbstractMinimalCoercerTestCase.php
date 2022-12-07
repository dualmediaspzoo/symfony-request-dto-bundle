<?php

namespace DM\DtoRequestBundle\Tests\PHPUnit\Coercer;

use DM\DtoRequestBundle\Interfaces\Type\CoercerInterface;
use DM\DtoRequestBundle\Model\Type\Property;
use DM\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;

abstract class AbstractMinimalCoercerTestCase extends KernelTestCase
{
    protected const BASIC_TYPES = [
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
            $this->fail('No service id is set');
        }

        self::bootKernel();
        $this->service = $this->getService(static::SERVICE_ID);
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(
        Property $property,
        bool $supports
    ): void {
        $this->assertEquals(
            $supports,
            $this->service->supports($property)
        );
    }

    /**
     * @return iterable<array{0: Property, 1: bool}>
     */
    abstract public function supportsProvider(): iterable;

    protected function buildProperty(
        string $type,
        bool $isCollection = false,
        ?string $class = null
    ): Property {
        return (new Property())
            ->setType($type)
            ->setCollection($isCollection)
            ->setFqcn($class);
    }
}
